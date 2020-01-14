# Dockerfile for AREDN MeshMap Docker Container.
# Run the AREDN MeshMap in a Docker Container
#
# Author:: Greg Albrecht W2GMD <oss@undef.net>
# Copyright:: Copyright 2020 Greg Albrecht
# License:: Apache License, Version 2.0
# Source:: https://github.com/ampledata/meshmap-docker
#

FROM mattrayner/lamp:latest-1804

# The following ENV variables should be overwritten at run time with:
# -e MYSQL_ADMIN_PASS="changeme",MYSQL_USER_PASS="changeme"
ENV MYSQL_ADMIN_PASS=changeme
ENV MYSQL_USER_PASS=changeme

# How often to poll the network for node information, defaults to 5 minutes (300s):
ENV POLLER_INTERVAL=300

# No need to change the following:
ENV MYSQL_USER_NAME=mesh-map
ENV MYSQL_USER_DB=node_map
ENV APACHE_ROOT=meshmap/webpage

# Git repo for the meshmap web app:
RUN git clone https://mapping.kg6wxc.net/git/meshmap

COPY scripts scripts
RUN chmod +x scripts/*.sh
RUN cp scripts/supervisor-meshmap.conf /etc/supervisor/conf.d/supervisor-meshmap.conf

# Allows us to override the startup cmd at runtime:
ENTRYPOINT ["./scripts/docker-entrypoint.sh"]
CMD ["meshmap"]

# Metadata about this container:
LABEL org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="MeshMap" \
      org.label-schema.description="Eric Satterlee KG6WXC's AREDN MeshMap." \
      org.label-schema.url="https://github.com/ampledata/meshmap-docker" \
      org.label-schema.vcs-url="https://github.com/ampledata/meshmap-docker" \
      org.label-schema.vcs-ref="https://github.com/ampledata/meshmap-docker" \
      org.label-schema.vendor="Greg Albrecht" \
      org.label-schema.version="$VERSION" \
      org.label-schema.schema-version="1.0" \
      org.label-schema.author="Greg Albrecht" \
      org.label-schema.docker.dockerfile="/Dockerfile" \
      org.label-schema.license="Apache License, Version 2.0" \
      org.label-schema.docker.cmd="docker run -d \
        -p 8888:80 \
        -e 'MYSQL_ADMIN_PASS=changeme'\
        -e 'MYSQL_USER_PASS=changeme' \
        -v `pwd`/meshmap-mysql:/var/lib/mysql \
        -v `pwd`/user-settings.ini:/meshmap/scripts/user-settings.ini \
        ampledata/meshmap" \
      maintainer="oss@undef.net"
