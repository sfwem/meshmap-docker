#!/bin/bash
#
# Runs the meshmap poller (get-map-info.php) in a 5 minute loop.
#

set -ex

while true; do
  cd /app/meshmap/scripts
  ./get-map-info.php
  sleep 300
done
