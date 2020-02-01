#!/bin/bash
# Wrapper script that creates the node-map Tables if they don't already exist, and starts the get-map-info poller
# in a loop with a POLLER_INTERVAL interval.
#
# Author:: Greg Albrecht W2GMD <oss@undef.net>
# Copyright:: Copyright 2020 Greg Albrecht
# License:: Apache License, Version 2.0
# Source:: https://github.com/ampledata/meshmap-docker
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

  if [[ -z $SYNC_DB && -z $CLEARDB_DATABASE_URL ]]; then
    sleep 1
    echo 'Backing up Database:'

    mysqldump -u admin --password=${MYSQL_ADMIN_PASS} --skip-triggers node_map > /var/lib/mysql/node_map_backup.sql
    mysql -v --reconnect \
      -u $(/scripts/extract_url.py -u) \
      --password=$(/scripts/extract_url.py -p) \
      --host=$(/scripts/extract_url.py -H) \
      $(/scripts/extract_url.py -d) \
      < /var/lib/mysql/node_map_backup.sql
  fi

  sleep ${POLLER_INTERVAL}
done

echo "Exiting."
