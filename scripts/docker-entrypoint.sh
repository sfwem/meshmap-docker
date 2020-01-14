#!/bin/bash
# docker-entrypoint.sh for AREDN MeshMap Docker Container.
#
# Author:: Greg Albrecht W2GMD <oss@undef.net>
# Copyright:: Copyright 2020 Greg Albrecht
# License:: Apache License, Version 2.0
# Source:: https://github.com/ampledata/meshmap-docker
#

set -ex

echo "Inside docker-entrypoint.sh"

if [ "$1" = "meshmap" ]; then
  echo "Starting meshmap"

  # 'app' is a Docker volume, so we can't copy this data over during build time:
  cp -pr meshmap app

  # Start supervisor, et al:
  ./run.sh

  # Exit after we've made our peace:
  exit
fi

echo "Executing '$@'"

exec "$@"
