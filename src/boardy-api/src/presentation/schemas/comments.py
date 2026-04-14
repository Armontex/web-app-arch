from pydantic import BaseModel


class CommentCreate(BaseModel):
    body: str


class CommentUpdate(BaseModel):
    body: str
