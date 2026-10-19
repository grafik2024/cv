#!/usr/bin/env python3
"""Minimal GDB remote client for the WinUAE gdbserver (test harness).

usage: gdbctl.py PORT CMD [CMD...]
  CMD is a WinUAE monitor command, e.g. "screenshot /tmp/a.png",
  "execution-status", "input event 2 1 4 1".
  Special CMDs: "stop" (interrupt), "cont" (continue), "detach".
"""
import socket
import sys
import time


def checksum(data: bytes) -> bytes:
    return b"%02x" % (sum(data) & 0xFF)


class Gdb:
    def __init__(self, port, timeout=20.0):
        deadline = time.time() + timeout
        while True:
            try:
                self.s = socket.create_connection(("127.0.0.1", port), timeout=5)
                break
            except OSError:
                if time.time() > deadline:
                    raise
                time.sleep(0.2)
        self.s.settimeout(timeout)
        self.buf = b""

    def _recv_packet(self):
        while True:
            start = self.buf.find(b"$")
            if start >= 0:
                end = self.buf.find(b"#", start)
                if end >= 0 and len(self.buf) >= end + 3:
                    payload = self.buf[start + 1:end]
                    self.buf = self.buf[end + 3:]
                    self.s.sendall(b"+")
                    return payload
            chunk = self.s.recv(65536)
            if not chunk:
                raise EOFError("connection closed")
            self.buf += chunk

    def send(self, payload: bytes, expect_reply=True):
        self.s.sendall(b"$" + payload + b"#" + checksum(payload))
        if not expect_reply:
            return None
        while True:
            pkt = self._recv_packet()
            # skip console output packets
            if pkt.startswith(b"O") and pkt != b"OK":
                continue
            return pkt

    def monitor(self, cmd: str) -> str:
        reply = self.send(b"qRcmd," + cmd.encode().hex().encode())
        try:
            return bytes.fromhex(reply.decode()).decode(errors="replace")
        except ValueError:
            return reply.decode(errors="replace")


def main():
    port = int(sys.argv[1])
    g = Gdb(port)
    # the server stops the CPU on attach and reports a stop reply
    print("attach:", g.send(b"?").decode(errors="replace"))
    for cmd in sys.argv[2:]:
        if cmd == "cont":
            g.send(b"c", expect_reply=False)
            print("cont")
        elif cmd == "stop":
            g.s.sendall(b"\x03")
            print("stop:", g._recv_packet().decode(errors="replace"))
        elif cmd == "detach":
            print("detach:", g.send(b"D").decode(errors="replace"))
        elif cmd.startswith("sleep "):
            time.sleep(float(cmd.split()[1]))
        else:
            print(cmd, "->", g.monitor(cmd))


if __name__ == "__main__":
    main()
