from __future__ import annotations

from sqlalchemy.ext.asyncio import (
    create_async_engine,
    AsyncSession,
    async_sessionmaker,
    AsyncEngine,
)

from ..config import Settings, get_settings


def create_engine(settings: Settings | None = None) -> AsyncEngine:
    active_settings = settings or get_settings()
    return create_async_engine(
        str(active_settings.db_mysql_url),
        echo=False,
    )


def create_session_factory(
    engine: AsyncEngine | None = None,
    settings: Settings | None = None,
) -> async_sessionmaker[AsyncSession]:
    active_engine = engine or create_engine(settings)
    return async_sessionmaker(
        bind=active_engine,
        class_=AsyncSession,
        autoflush=False,
        autocommit=False,
        expire_on_commit=False,
    )
