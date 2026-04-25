from fastapi import Depends, Request
from sqlalchemy.ext.asyncio import AsyncSession


def get_session(request: Request) -> AsyncSession:
    return request.app.state.session_maker()
