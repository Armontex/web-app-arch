from __future__ import annotations

import logging
from typing import Any


class StatusEndpointAccessFilter(logging.Filter):
    def filter(self, record: logging.LogRecord) -> bool:
        request_path = _extract_request_path(record.args)
        return request_path != "/status"


def configure_access_logging() -> None:
    logger = logging.getLogger("uvicorn.access")

    if any(isinstance(item, StatusEndpointAccessFilter) for item in logger.filters):
        return

    logger.addFilter(StatusEndpointAccessFilter())


def _extract_request_path(args: Any) -> str | None:
    if not isinstance(args, tuple):
        return None

    if len(args) < 3:
        return None

    request_path = args[2]
    return request_path if isinstance(request_path, str) else None


__all__ = [
    "StatusEndpointAccessFilter",
    "configure_access_logging",
]
