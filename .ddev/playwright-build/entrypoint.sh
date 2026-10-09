#!/usr/bin/env bash
set -e

# Start VNC server if KASMVNC_VERSION is set
if [ -n "$KASMVNC_VERSION" ] && [ -f /usr/bin/vncserver ]; then
  vncserver -depth 24 -geometry 1920x1080 :1
fi

# Execute CMD
exec "$@"
