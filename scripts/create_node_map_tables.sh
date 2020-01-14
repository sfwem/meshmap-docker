#!/bin/bash

set -ex

RET=1
while [[ RET -ne 0 ]]; do
    echo "=> create_node_map_tables.sh: Waiting for MySQL to be available to create mesh-map tables..."
    sleep 10
    echo "=> create_node_map_tables.sh: Running 'mysql < /app/meshmap/node_map.sql', ignore any errors (will automatically re-try):"
    mysql < /app/meshmap/node_map.sql
    RET=$?
    echo "=> create_node_map_tables.sh: Return Code: $RET"
done

echo "=> create_node_map_tables.sh: Done!"
