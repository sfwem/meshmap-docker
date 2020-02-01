#!/bin/bash
# Wrapper script that creates the node-map Tables if they don't already exist, and starts the get-map-info poller
# in a loop with a POLLER_INTERVAL interval.
#
# Developed for the San Francisco Wireless Emergency Mesh project: https://www.sfwem.net
#
# Author:: Greg Albrecht W2GMD <oss@undef.net>
# Copyright:: Copyright 2020 Greg Albrecht
# License:: Apache License, Version 2.0
# Source:: https://github.com/sfwem/meshmap-docker
#

set -x

echo "Checking to see if mysqld is running..."
RET=1
while [[ RET -ne 0 ]]; do
    echo "Waiting for confirmation of MySQL service startup"
    sleep 5
    mysql -uroot -e "status"
    RET=$?
done

# Create the mysql tables (if they don't already exist):
echo "Checking to see if node_map Tables have been created..."
mysql -uroot node_map -e 'SELECT * FROM users;'
SELECT_RETURN=$?

if [ ${SELECT_RETURN} -ne 0 ]; then
  RET=1
  while [[ RET -ne 0 ]]; do
      echo "Trying to create node_map Tables..."
      mysql < /app/meshmap/node_map.sql
      RET=$?
  done
  echo "Created node_map Tables."
else
  echo "Looks like node_map Tables have been created."
fi

echo "Starting get-map-info.php Loop with ${POLLER_INTERVAL} interval."

while true; do
  echo 'Running Poller:'
  cd /app/meshmap/scripts
  ./get-map-info.php
  ./sync_db.sh
  sleep ${POLLER_INTERVAL}
done

echo "Exiting."
