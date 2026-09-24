#!/bin/sh
# RVM kiosk launcher: waits until the local web app answers, then starts Chromium.
# Logs to ~/kiosk-boot.log so a blank screen after boot can be diagnosed.
LOG="$HOME/kiosk-boot.log"
exec >>"$LOG" 2>&1
echo "=== $(date '+%F %T') kiosk launcher start (uptime: $(cut -d' ' -f1 /proc/uptime)s)"
echo "env: DISPLAY=$DISPLAY WAYLAND_DISPLAY=$WAYLAND_DISPLAY XDG_SESSION_TYPE=$XDG_SESSION_TYPE"

i=0
until curl -sk -o /dev/null --max-time 3 https://localhost/; do
    i=$((i + 1))
    [ "$i" -ge 90 ] && { echo "web app not ready after 90s, starting anyway"; break; }
    sleep 1
done
echo "web app check finished after ${i}s"
sleep 3

echo "$(date '+%T') launching chromium"
exec chromium --kiosk --password-store=basic --noerrdialogs --disable-infobars --incognito \
    --disable-session-crashed-bubble --check-for-update-interval=31536000 \
    --enable-logging=stderr --v=0 \
    "https://localhost/#/kiosk/RVM-001"
