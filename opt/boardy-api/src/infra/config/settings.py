from functools import lru_cache
from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict

from .app import AppSettings
from .db import DatabaseSettings


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
        env_nested_delimiter="__",
    )

    app: AppSettings = Field(default_factory=AppSettings)
    db: DatabaseSettings = Field(default_factory=DatabaseSettings)


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


__all__ = [
    "get_settings",
    "Settings",
]
