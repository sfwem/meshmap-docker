#!/bin/bash
# Backs-up & Sync database to remote viewer (e.g. Heroku Instance).
#
# Developed for the San Francisco Wireless Emergency Mesh project: https://www.sfwem.net
#
# Author:: Greg Albrecht W2GMD <oss@undef.net>
# Copyright:: Copyright 2020 Greg Albrecht
# License:: Apache License, Version 2.0
# Source:: https://github.com/sfwem/meshmap-docker
#


if [[ ! -z $SYNC_DB && ! -z $CLEARDB_DATABASE_URL ]]; then
  echo 'Backing up Database:'
  mysqldump -u admin \
    --password=${MYSQL_ADMIN_PASS} \
    --skip-triggers node_map > /var/lib/mysql/node_map_backup.sql

  echo 'Syncing Database:'
  mysql -v --reconnect \
    -u $(/scripts/extract_url.py -u) \
    --password=$(/scripts/extract_url.py -p) \
    --host=$(/scripts/extract_url.py -H) \
    $(/scripts/extract_url.py -d) \
    < /var/lib/mysql/node_map_backup.sql
fi
