from pydantic import BaseModel


class AppSettings(BaseModel):
    debug: bool = True


__all__ = [
    "AppSettings",
]
