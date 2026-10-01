# ai_service_4bin — archived 4-bin sorter service

This is the original Python service for the Pi "adi" prototype: a camera,
YOLO classification, and two pan/tilt servos that drop each item straight
into one of 4 bins (aluminum, glass, plastic, paper).

The active service is `BackEnd/ai_service`, which drives the 2-bin DSME
compactor machine. The systemd unit (`deploy/rvm-ai.service`) and
`FrontEnd/vite.config.js` always point at `BackEnd/ai_service`, so to run
this 4-bin version instead, swap the folder names:

    mv BackEnd/ai_service BackEnd/ai_service_2bin
    mv BackEnd/ai_service_4bin BackEnd/ai_service

and restart the service. The kiosk detects this on its own: this service has
no `/state` endpoint, so `/api/hardware/state` reports `profile: "legacy"`
and the kiosk uses the old `/hardware/sort` flow.

Copy `.env` and `model/` along with it (both are gitignored).
