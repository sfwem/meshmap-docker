#!/bin/bash

set -ex

echo "Inside docker-entrypoint.sh"

if [ "$1" = "mesh-map" ]; then
  echo "Starting mesh-map"
  echo nameserver $TOPO_HOST > /etc/resolv.conf

  cp -pr meshmap app
  cp user-settings.ini app/meshmap/scripts/user-settings.ini
  ./scripts/create_node_map_tables.sh &
  ./run.sh
  exit
fi

echo "Executing '$@'"

exec "$@"
