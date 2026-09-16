#!/usr/bin/env python3
"""Local-only companion for the RVM kiosk's admin "Maintenance" action.

Runs as the SAME desktop user as the kiosk's Chromium (started via
rvm-kiosk-control-autostart.desktop, alongside rvm-kiosk-autostart.desktop),
so it can kill that Chromium without sudo. Binds to 127.0.0.1 only — never
exposed through Nginx — because the only caller is the Laravel backend
running on this same physical machine (see
AdminController::maintainMachine(), config('services.kiosk_control.url')).
No auth token here: reaching 127.0.0.1 already means "a process on this box
called me," which on a self-contained kiosk-per-Pi deployment means Laravel.
"""

import subprocess
from http.server import BaseHTTPRequestHandler, HTTPServer

HOST = "127.0.0.1"
PORT = 8765


class Handler(BaseHTTPRequestHandler):
    def do_POST(self):
        if self.path != "/exit":
            self.send_response(404)
            self.end_headers()
            return

        # Only the kiosk's own flagged Chromium — not any other browser
        # window an admin might have open on this desktop for maintenance.
        subprocess.run(["pkill", "-f", "chromium.*--kiosk"], check=False)

        body = b'{"success": true}'
        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def log_message(self, format, *args):
        pass  # keep this quiet — journalctl already timestamps stdout/stderr


if __name__ == "__main__":
    HTTPServer((HOST, PORT), Handler).serve_forever()
