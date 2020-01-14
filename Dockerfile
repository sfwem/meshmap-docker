#
# MeshMap Docker
# Run the AREDN MeshMap in a Docker Container
#
FROM mattrayner/lamp:latest-1804

# The following ENV variables should be overwritten at run time with:
# -e MYSQL_ADMIN_PASS="changeme",MYSQL_USER_PASS="changeme"
ENV MYSQL_ADMIN_PASS=changeme
ENV MYSQL_USER_PASS=changeme

# TBD:
ENV TOPO_HOST=10.113.28.81

# No need to change the following:
ENV MYSQL_USER_NAME=mesh-map
ENV MYSQL_USER_DB=node_map
ENV APACHE_ROOT=meshmap/webpage

RUN git clone https://mapping.kg6wxc.net/git/meshmap

ADD . .

RUN chmod +x scripts/*.sh

ADD scripts/supervisor-poller.conf /etc/supervisor/conf.d/supervisor-poller.conf

ENTRYPOINT ["./scripts/docker-entrypoint.sh"]

CMD ["mesh-map"]

LABEL org.label-schema.build-date=$BUILD_DATE \
      org.label-schema.name="MeshMap" \
      org.label-schema.description="Eric Satterlee KG6WXC's AREDN MeshMap." \
      org.label-schema.url="https://github.com/ampledata/meshmap-docker" \
      org.label-schema.vcs-url=$VCS_URL \
      org.label-schema.vcs-ref=$VCS_REF \
      org.label-schema.vendor="$VENDOR" \
      org.label-schema.version="$VERSION" \
      org.label-schema.schema-version="1.0" \
      org.label-schema.author="$AUTHOR" \
      org.label-schema.docker.dockerfile="/Dockerfile" \
      org.label-schema.license="Apache License, Version 2.0" \
      maintainer="oss@undef.net"
