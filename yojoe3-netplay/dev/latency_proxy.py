#!/usr/bin/env python3
"""TCP proxy that adds one-way latency (+ random jitter) in both directions.

usage: latency_proxy.py LISTEN_PORT TARGET_PORT DELAY_MS [JITTER_MS]
"""
import asyncio
import random
import sys
import time

LISTEN, TARGET = int(sys.argv[1]), int(sys.argv[2])
DELAY = float(sys.argv[3]) / 1000.0
JITTER = float(sys.argv[4]) / 1000.0 if len(sys.argv) > 4 else 0.0


async def pipe(reader, writer):
    queue = asyncio.Queue()

    async def sender():
        last = 0.0
        while True:
            due, data = await queue.get()
            if data is None:
                break
            # keep TCP ordering: never send earlier than the previous chunk
            due = max(due, last)
            last = due
            wait = due - time.monotonic()
            if wait > 0:
                await asyncio.sleep(wait)
            writer.write(data)
            await writer.drain()
        writer.close()

    task = asyncio.create_task(sender())
    while True:
        data = await reader.read(65536)
        if not data:
            break
        queue.put_nowait((time.monotonic() + DELAY + random.uniform(0, JITTER), data))
    queue.put_nowait((0, None))
    await task


async def handle(client_reader, client_writer):
    server_reader, server_writer = await asyncio.open_connection("127.0.0.1", TARGET)
    await asyncio.gather(pipe(client_reader, server_writer), pipe(server_reader, client_writer),
                         return_exceptions=True)


async def main():
    server = await asyncio.start_server(handle, "127.0.0.1", LISTEN)
    async with server:
        await server.serve_forever()


asyncio.run(main())
