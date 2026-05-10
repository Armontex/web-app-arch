from __future__ import annotations

from typing import AsyncGenerator

from fastapi import APIRouter, Depends, HTTPException, Request, status
from pydantic import BaseModel
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from database import Comment, Post, User


router = APIRouter()


class CommentCreate(BaseModel):
    body: str


class CommentUpdate(BaseModel):
    body: str


async def get_session(request: Request) -> AsyncGenerator[AsyncSession, None]:
    async with request.app.state.session_maker() as session:
        yield session


@router.get("/posts/{post_id}/comments")
async def get_comments(
    post_id: int,
    session: AsyncSession = Depends(get_session),
) -> dict[str, list[dict[str, object]] | int]:
    query = (
        select(
            Comment.id,
            Comment.body,
            Comment.post_id,
            Comment.author_id,
            Comment.created_at,
            User.name.label("author_name"),
        )
        .join(User, Comment.author_id == User.id)
        .where(Comment.post_id == post_id)
        .order_by(Comment.created_at)
    )
    result = await session.execute(query)

    items = [
        {
            "id": row.id,
            "body": row.body,
            "post_id": row.post_id,
            "author_id": row.author_id,
            "created_at": str(row.created_at),
            "author_name": row.author_name,
        }
        for row in result.all()
    ]

    return {"items": items, "count": len(items)}


@router.post("/posts/{post_id}/comments", status_code=status.HTTP_201_CREATED)
async def create_comment(
    post_id: int,
    data: CommentCreate,
    session: AsyncSession = Depends(get_session),
) -> dict[str, int | str]:
    if not data.body.strip():
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Текст пустой",
        )

    post = await session.get(Post, post_id)
    if post is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Пост не найден",
        )

    # API комментариев пока без авторизации, автор фиксированный.
    new_comment = Comment(body=data.body, post_id=post_id, author_id=1)
    session.add(new_comment)
    await session.commit()
    await session.refresh(new_comment)

    return {
        "id": new_comment.id,
        "body": new_comment.body,
        "status": "created",
    }


@router.put("/comments/{comment_id}")
async def update_comment(
    comment_id: int,
    data: CommentUpdate,
    session: AsyncSession = Depends(get_session),
) -> dict[str, int | str]:
    if not data.body.strip():
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Текст пустой",
        )

    comment = await session.get(Comment, comment_id)
    if comment is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Не найден",
        )

    comment.body = data.body
    await session.commit()
    await session.refresh(comment)

    return {
        "id": comment.id,
        "body": comment.body,
        "status": "updated",
    }


@router.delete("/comments/{comment_id}", status_code=status.HTTP_204_NO_CONTENT)
async def delete_comment(
    comment_id: int,
    session: AsyncSession = Depends(get_session),
) -> None:
    comment = await session.get(Comment, comment_id)
    if comment is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Не найден",
        )

    await session.delete(comment)
    await session.commit()
