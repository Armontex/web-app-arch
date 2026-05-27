#!/usr/bin/env bash
set -euo pipefail

docker compose exec -T api python - <<'PY'
import asyncio
import json

from redis.asyncio import Redis
import websockets


async def main() -> None:
    async with websockets.connect("ws://127.0.0.1:8000/ws") as ws:
        redis = Redis.from_url("redis://redis:6379/0", decode_responses=True)
        await redis.publish(
            "new_post",
            json.dumps(
                {
                    "id": 1701,
                    "title": "Task 17 Redis subscriber",
                    "body": "Published from verification script",
                    "author": "Verifier",
                    "created_at": "2026-05-27T09:17:00Z",
                }
            ),
        )
        await redis.aclose()

        message = await asyncio.wait_for(ws.recv(), timeout=5)
        print(message)


asyncio.run(main())
PY
