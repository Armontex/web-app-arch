from contextlib import asynccontextmanager
import asyncio
import json
import logging
import os

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from redis.asyncio import Redis
from sqlalchemy import update
from sqlalchemy.ext.asyncio import AsyncSession, async_sessionmaker

from database import Base, Comment, create_engine, create_session_factory
from routers import ws
from routers.comments import router as comments_router


logger = logging.getLogger("uvicorn.error")


async def handle_user_renamed(
    session_maker: async_sessionmaker[AsyncSession],
    event: dict[str, object],
) -> None:
    user_id = int(event["user_id"])
    name = str(event["name"])

    async with session_maker() as session:
        result = await session.execute(
            update(Comment)
            .where(Comment.author_id == user_id)
            .values(author_name=name)
        )
        await session.commit()

    logger.info(
        "Updated comments author_name for user %s: %s rows",
        user_id,
        result.rowcount,
    )


async def subscribe_to_redis_events(
    redis_url: str,
    session_maker: async_sessionmaker[AsyncSession],
) -> None:
    redis = Redis.from_url(redis_url, decode_responses=True)
    pubsub = redis.pubsub()

    try:
        await pubsub.subscribe("new_post", "user.renamed")
        logger.info("Subscribed to Redis channels: new_post, user.renamed")

        async for message in pubsub.listen():
            if message["type"] != "message":
                continue

            event = json.loads(message["data"])

            if message["channel"] == "new_post":
                logger.info("Received Redis new_post event: %s", event.get("id"))
                await ws.manager.broadcast({"type": "new_post", "post": event})
            elif message["channel"] == "user.renamed":
                logger.info(
                    "Received Redis user.renamed event: %s",
                    event.get("user_id"),
                )
                await handle_user_renamed(session_maker, event)
    except asyncio.CancelledError:
        raise
    finally:
        await pubsub.unsubscribe("new_post", "user.renamed")
        await pubsub.close()
        await redis.aclose()


@asynccontextmanager
async def lifespan(app: FastAPI):
    engine = create_engine()
    session_maker = create_session_factory(engine)

    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

    app.state.session_maker = session_maker
    app.state.engine = engine
    app.state.redis_subscriber = asyncio.create_task(
        subscribe_to_redis_events(
            os.getenv("REDIS_URL", "redis://redis:6379/0"),
            session_maker,
        )
    )

    try:
        yield
    finally:
        app.state.redis_subscriber.cancel()
        try:
            await app.state.redis_subscriber
        except asyncio.CancelledError:
            pass

        await engine.dispose()


app = FastAPI(
    title="Boardy API",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        origin.strip()
        for origin in os.getenv(
            "FRONTEND_ORIGINS",
            "http://localhost,http://localhost:8000",
        ).split(",")
        if origin.strip()
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

app.include_router(comments_router, prefix="/api")
app.include_router(ws.router)


@app.get("/status")
async def status():
    return {"status": "ok"}


@app.get("/health")
async def health():
    return {"ok": True}


@app.get("/api/health")
async def api_health():
    return {"ok": True}


@app.post("/internal/broadcast")
async def internal_broadcast(request: Request) -> dict[str, bool]:
    data = await request.json()

    if "type" in data:
        await ws.manager.broadcast(data)
    else:
        await ws.manager.broadcast({"type": "new_post", "post": data})

    return {"ok": True}
