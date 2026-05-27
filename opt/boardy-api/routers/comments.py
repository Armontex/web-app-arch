from __future__ import annotations

from datetime import UTC, datetime
from typing import AsyncGenerator

from fastapi import APIRouter, Depends, HTTPException, Request, Response, status
from pydantic import BaseModel
from sqlalchemy import select
from sqlalchemy.ext.asyncio import AsyncSession

from auth import get_current_user
from database import Comment
from routers import ws


router = APIRouter()


class CommentCreate(BaseModel):
    body: str
    author_name: str


class CommentUpdate(BaseModel):
    body: str


def current_user_id(user: dict[str, object]) -> int:
    return int(user.get("sub") or user.get("user_id"))


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
            Comment.author_name,
            Comment.created_at,
        )
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


def comment_payload(comment: Comment) -> dict[str, int | str]:
    created_at = comment.created_at or datetime.now(UTC)

    return {
        "id": comment.id,
        "post_id": comment.post_id,
        "author_id": comment.author_id,
        "author_name": comment.author_name,
        "body": comment.body,
        "created_at": created_at.isoformat(),
    }


@router.post("/posts/{post_id}/comments", status_code=status.HTTP_201_CREATED)
async def create_comment(
    post_id: int,
    data: CommentCreate,
    user: dict[str, object] = Depends(get_current_user),
    session: AsyncSession = Depends(get_session),
) -> dict[str, int | str]:
    if not data.body.strip():
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Текст пустой",
        )
    if not data.author_name.strip():
        raise HTTPException(
            status_code=status.HTTP_422_UNPROCESSABLE_ENTITY,
            detail="Имя автора пустое",
        )

    author_id = current_user_id(user)
    new_comment = Comment(
        body=data.body,
        post_id=post_id,
        author_id=author_id,
        author_name=data.author_name.strip(),
    )
    session.add(new_comment)
    await session.commit()
    await session.refresh(new_comment)

    payload = comment_payload(new_comment)
    await ws.manager.broadcast({"type": "comment.created", "comment": payload})

    return {**payload, "status": "created"}


@router.put("/comments/{comment_id}")
async def update_comment(
    comment_id: int,
    data: CommentUpdate,
    user: dict[str, object] = Depends(get_current_user),
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
    if comment.author_id != current_user_id(user):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not your comment",
        )

    comment.body = data.body
    await session.commit()
    await session.refresh(comment)

    payload = comment_payload(comment)
    await ws.manager.broadcast({"type": "comment.updated", "comment": payload})

    return {**payload, "status": "updated"}


@router.delete(
    "/comments/{comment_id}",
    status_code=status.HTTP_204_NO_CONTENT,
    response_class=Response,
)
async def delete_comment(
    comment_id: int,
    user: dict[str, object] = Depends(get_current_user),
    session: AsyncSession = Depends(get_session),
) -> Response:
    comment = await session.get(Comment, comment_id)
    if comment is None:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="Не найден",
        )
    if comment.author_id != current_user_id(user):
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail="Not your comment",
        )

    payload = comment_payload(comment)
    await session.delete(comment)
    await session.commit()
    await ws.manager.broadcast({"type": "comment.deleted", "comment": payload})

    return Response(status_code=status.HTTP_204_NO_CONTENT)
