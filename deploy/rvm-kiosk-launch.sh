#!/bin/sh
# RVM kiosk launcher: waits until the local web app answers, then starts Chromium
# and watches its startup. Logs to ~/kiosk-boot.log so a blank screen after boot
# can be diagnosed.
#
# Watchdog: on a busy boot (1 GB Pi 4 swapping while MariaDB/PHP/rvm-ai load),
# Chromium's network-service child can time out ("15 seconds with no
# connection"), Chromium restarts it, and the initial navigation is lost, so the
# kiosk stays a blank grey window. NetworkServiceInProcess2 (below) removes that
# child process; the watchdog stays as a safety net: if the crash still happens,
# or Chromium never requests the page from nginx, Chromium is killed and started
# again. Once the page has
# loaded the launcher just waits; if Chromium exits on its own (e.g. the admin
# Maintenance action via kiosk-control.py) it is NOT restarted.
LOG="$HOME/kiosk-boot.log"
URL="https://localhost/#/kiosk/RVM-001"
ACCESS_LOG=/var/log/nginx/access.log  # readable via the adm group; load check skipped if not
LOAD_TIMEOUT=90                       # seconds to wait for Chromium's first request
MAX_ATTEMPTS=4
RETRY_DELAY=3

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

# rvm-ai (torch/YOLO import: about a minute of heavy CPU + RAM) is started only
# once the kiosk page is up, so it doesn't fight Chromium on a 1 GB Pi during
# boot. Needs deploy/sudoers-rvm-kiosk; deploy/rvm-ai.timer is the fallback if
# this launcher never gets that far. No-op if rvm-ai is already running.
start_ai() {
    if sudo -n systemctl start --no-block rvm-ai.service; then
        echo "$(date '+%T') started rvm-ai"
    else
        echo "$(date '+%T') could not start rvm-ai (sudoers rule missing?)"
    fi
}

# Uptime instead of date: the wall clock jumps when NTP syncs during boot.
uptime_s() { cut -d. -f1 /proc/uptime; }
size_of() { wc -c <"$1" 2>/dev/null || echo 0; }

# Bytes appended to $1 since offset $2 (offset reset if the file was rotated).
new_bytes() {
    [ "$(size_of "$1")" -lt "$2" ] && set -- "$1" 0
    tail -c +"$(($2 + 1))" "$1" 2>/dev/null
}

attempt=1
while :; do
    log_off=$(size_of "$LOG")
    acc_off=$(size_of "$ACCESS_LOG")
    echo "$(date '+%T') launching chromium (attempt $attempt/$MAX_ATTEMPTS)"
    # NetworkServiceInProcess2: run the network service inside the browser
    # process, so there is no separate child that can hit the 15s timeout.
    chromium --kiosk --password-store=basic --noerrdialogs --disable-infobars --incognito \
        --enable-features=NetworkServiceInProcess2 \
        --disable-session-crashed-bubble --check-for-update-interval=31536000 \
        --enable-logging=stderr --v=0 \
        "$URL" &
    pid=$!
    start=$(uptime_s)

    status=""
    while kill -0 "$pid" 2>/dev/null; do
        if new_bytes "$LOG" "$log_off" | grep -q 'Network service crashed'; then
            status=crashed; break
        fi
        if [ -r "$ACCESS_LOG" ]; then
            # Only this machine's own browser (::1/127.0.0.1), not phones or curl.
            if new_bytes "$ACCESS_LOG" "$acc_off" | grep -E '^(::1|127\.0\.0\.1) ' | grep -q 'Chrome/'; then
                status=loaded; break
            fi
        fi
        if [ $(($(uptime_s) - start)) -ge "$LOAD_TIMEOUT" ]; then
            if [ -r "$ACCESS_LOG" ]; then status=timeout; else status=unverified; fi
            break
        fi
        sleep 2
    done

    if [ -z "$status" ]; then
        wait "$pid"
        echo "$(date '+%T') watchdog: chromium exited during startup (code $?), not restarting"
        start_ai
        exit 0
    fi

    echo "$(date '+%T') watchdog: $status after $(($(uptime_s) - start))s"
    if [ "$status" = loaded ] || [ "$status" = unverified ] || [ "$attempt" -ge "$MAX_ATTEMPTS" ]; then
        [ "$status" = loaded ] || [ "$status" = unverified ] || echo "watchdog: giving up after $attempt attempts"
        start_ai
        wait "$pid"
        exit 0
    fi

    echo "watchdog: restarting chromium in ${RETRY_DELAY}s"
    kill "$pid" 2>/dev/null
    n=0
    while kill -0 "$pid" 2>/dev/null && [ "$n" -lt 10 ]; do sleep 1; n=$((n + 1)); done
    kill -9 "$pid" 2>/dev/null
    wait "$pid" 2>/dev/null
    sleep "$RETRY_DELAY"
    attempt=$((attempt + 1))
done
