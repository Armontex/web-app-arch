from __future__ import annotations

from datetime import datetime

from sqlalchemy import ForeignKey, String, Text, text
from sqlalchemy.orm import Mapped, mapped_column, relationship

from .base import Base


class User(Base):
    __tablename__ = "users"
    __table_args__ = {"mysql_engine": "InnoDB"}

    id: Mapped[int] = mapped_column(init=False, primary_key=True)
    name: Mapped[str] = mapped_column(String(100), nullable=False)
    email: Mapped[str] = mapped_column(String(255), nullable=False, unique=True)
    password_hash: Mapped[str] = mapped_column(String(255), nullable=False)
    created_at: Mapped[datetime] = mapped_column(
        init=False,
        server_default=text("CURRENT_TIMESTAMP"),
        nullable=False,
    )

    posts: Mapped[list["Post"]] = relationship(
        back_populates="author",
        default_factory=list,
        passive_deletes=True,
        init=False,
    )
    comments: Mapped[list["Comment"]] = relationship(
        back_populates="author",
        default_factory=list,
        passive_deletes=True,
        init=False,
    )


class Post(Base):
    __tablename__ = "posts"
    __table_args__ = {"mysql_engine": "InnoDB"}

    id: Mapped[int] = mapped_column(init=False, primary_key=True)
    title: Mapped[str] = mapped_column(String(255), nullable=False)
    body: Mapped[str] = mapped_column(Text, nullable=False)
    author_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE"),
        nullable=False,
    )
    created_at: Mapped[datetime] = mapped_column(
        init=False,
        server_default=text("CURRENT_TIMESTAMP"),
        nullable=False,
    )

    author: Mapped["User"] = relationship(
        back_populates="posts",
        init=False,
    )
    comments: Mapped[list["Comment"]] = relationship(
        back_populates="post",
        default_factory=list,
        passive_deletes=True,
        init=False,
    )


class Comment(Base):
    __tablename__ = "comments"
    __table_args__ = {"mysql_engine": "InnoDB"}

    id: Mapped[int] = mapped_column(init=False, primary_key=True)
    body: Mapped[str] = mapped_column(Text, nullable=False)
    post_id: Mapped[int] = mapped_column(
        ForeignKey("posts.id", ondelete="CASCADE"),
        nullable=False,
    )
    author_id: Mapped[int] = mapped_column(
        ForeignKey("users.id", ondelete="CASCADE"),
        nullable=False,
    )
    created_at: Mapped[datetime] = mapped_column(
        init=False,
        server_default=text("CURRENT_TIMESTAMP"),
        nullable=False,
    )

    post: Mapped["Post"] = relationship(back_populates="comments", init=False)
    author: Mapped["User"] = relationship(back_populates="comments", init=False)
