from contextlib import asynccontextmanager

from fastapi import FastAPI, Request
from fastapi.middleware.cors import CORSMiddleware

from database import Base, create_engine, create_session_factory
from routers import ws
from routers.comments import router as comments_router


@asynccontextmanager
async def lifespan(app: FastAPI):
    engine = create_engine()
    session_maker = create_session_factory(engine)

    async with engine.begin() as conn:
        await conn.run_sync(Base.metadata.create_all)

    app.state.session_maker = session_maker
    app.state.engine = engine

    try:
        yield
    finally:
        await engine.dispose()


app = FastAPI(
    title="Boardy API",
    version="1.0.0",
    lifespan=lifespan,
)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost"],
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
