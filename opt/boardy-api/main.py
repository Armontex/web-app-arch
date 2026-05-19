from contextlib import asynccontextmanager

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from database import Base, create_engine, create_session_factory
from routers.comments import router as comments_router
from routers.ws import router as ws_router


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
app.include_router(ws_router)


@app.get("/status")
async def status():
    return {"status": "ok"}
