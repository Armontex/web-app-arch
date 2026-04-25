from pydantic import BaseModel, computed_field
from pydantic.networks import MySQLDsn


class DatabaseSettings(BaseModel):
    host: str = "127.0.0.1"
    port: int = 3306
    user: str = "boardy"
    password: str = "boardy"
    name: str = "boardy"
    charset: str = "utf8mb4"
    echo: bool = False

    @computed_field
    @property
    def mysql_url(self) -> MySQLDsn:
        return MySQLDsn.build(
            scheme="mysql+aiomysql",
            host=self.host,
            port=self.port,
            username=self.user,
            password=self.password,
            path=self.name,
            query=f"charset={self.charset}",
        )


__all__ = [
    "DatabaseSettings",
]
