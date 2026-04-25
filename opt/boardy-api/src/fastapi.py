from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager

from src.infra.config import get_settings
from src.infra.db import create_session_factory, create_engine

from src.infra.db.base import Base
import src.infra.db.models

from src.presentation.routers import router as api_router


@asynccontextmanager
async def lifespan(app: FastAPI):
    engine = create_engine(app.state.settings)
    session_maker = create_session_factory(engine)

    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

    app.state.session_maker = session_maker
    app.state.engine = engine

    try:
        yield
    finally:
        await engine.dispose()


def create_app():
    settings = get_settings()

    app = FastAPI(
        title="Boardy API",
        version="1.0.0",
        debug=settings.app.debug,
        lifespan=lifespan,
    )

    app.add_middleware(
        CORSMiddleware,
        allow_origins=[
            "http:localhost",
        ],
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )

    app.include_router(api_router)

    app.state.settings = settings

    @app.get(path="/status")
    async def status():
        return {"status": "ok"}

    return app


# @app.get("/api/status")
# async def status():
#     return {"status": "ok", "time": str(datetime.now())}


# @app.get("/api/messages")
# async def get_messages():
#     conn = await get_db()
#     async with conn.cursor(aiomysql.DictCursor) as cur:
#         await cur.execute(
#             "SELECT posts.body AS message, users.name, "
#             "posts.created_at FROM posts "
#             "JOIN users ON posts.author_id = users.id "
#             "ORDER BY posts.created_at DESC"
#         )
#         messages = await cur.fetchall()
#     conn.close()
#     for m in messages:
#         m["created_at"] = str(m["created_at"])
#     return {"messages": messages, "count": len(messages)}


# @app.get("/api/users")
# async def get_users():
#     conn = await get_db()
#     async with conn.cursor(aiomysql.DictCursor) as cur:
#         await cur.execute("SELECT id, name, email, created_at FROM users")
#         users = await cur.fetchall()
#     conn.close()
#     for u in users:
#         u["created_at"] = str(u["created_at"])
#     return {"users": users, "count": len(users)}
