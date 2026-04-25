from pydantic_settings import BaseSettings, SettingsConfigDict
from pydantic import computed_field
from pydantic.networks import MySQLDsn
from functools import lru_cache


class Settings(BaseSettings):

    model_config = SettingsConfigDict(
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    debug: bool = True

    db_host: str = "127.0.0.1"
    db_port: int = 3306
    db_user: str = "boardy"
    db_password: str = "boardy"
    db_name: str = "boardy"
    db_charset: str = "utf8mb4"

    @computed_field
    @property
    def db_mysql_url(self) -> MySQLDsn:
        return MySQLDsn.build(
            scheme="mysql+aiomysql",
            host=self.db_host,
            port=self.db_port,
            username=self.db_user,
            password=self.db_password,
            path=self.db_name,
            query=f"charset={self.db_charset}",
        )


@lru_cache(maxsize=1)
def get_settings() -> Settings:
    return Settings()


__all__ = [
    "get_settings",
    "Settings",
]
