from contextlib import asynccontextmanager
import asyncio
import json
import logging
import os

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware
from redis.asyncio import Redis

from database import Base, create_engine, create_session_factory
from routers import ws
from routers.comments import router as comments_router


logger = logging.getLogger("uvicorn.error")


async def subscribe_to_new_posts(redis_url: str) -> None:
    redis = Redis.from_url(redis_url, decode_responses=True)
    pubsub = redis.pubsub()

    try:
        await pubsub.subscribe("new_post")
        logger.info("Subscribed to Redis channel: new_post")

        async for message in pubsub.listen():
            if message["type"] != "message":
                continue

            post = json.loads(message["data"])
            logger.info("Received Redis new_post event: %s", post.get("id"))
            await ws.manager.broadcast({"type": "new_post", "post": post})
    except asyncio.CancelledError:
        raise
    finally:
        await pubsub.unsubscribe("new_post")
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
        subscribe_to_new_posts(os.getenv("REDIS_URL", "redis://redis:6379/0"))
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


@app.post("/internal/broadcast")
async def internal_broadcast(request: Request) -> dict[str, bool]:
    data = await request.json()
    await ws.manager.broadcast({"type": "new_post", "post": data})
    return {"ok": True}
