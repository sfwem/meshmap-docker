# Makefile for AREDN MeshMap Docker Container.
#
# Author:: Greg Albrecht W2GMD <oss@undef.net>
# Copyright:: Copyright 2020 Greg Albrecht
# License:: Apache License, Version 2.0
# Source:: https://github.com/ampledata/meshmap-docker
#

default: build

# Build Docker image
build: date docker_build output date

# Build and push Docker image
release: docker_build docker_push output

clean:
	rm -f user-settings.ini
	rm -rf meshmap-mysql

run: make_mysql_dir get_user_settings docker_run

# Create the mysql persistent directory:
make_mysql_dir:
	mkdir -p meshmap-mysql

# Download the user-settings.ini to allow local modifications:
get_user_settings:
	curl -so user-settings.ini https://gitlab.kg6wxc.net/mesh/meshmap/raw/master/scripts/user-settings.ini-default

# Image and binary can be overidden with env vars.
DOCKER_IMAGE ?= ampledata/meshmap

# Get the latest commit.
GIT_COMMIT = $(shell git rev-parse --short HEAD)

# Get the version number from the code
CODE_VERSION = $(shell cat VERSION)

# Find out if the working directory is clean
GIT_NOT_CLEAN_CHECK = $(shell git status --porcelain)
ifneq (x$(GIT_NOT_CLEAN_CHECK), x)
  DOCKER_TAG_SUFFIX = -dirty
endif

# If we're releasing to Docker Hub, and we're going to mark it with the latest tag,
#  it should exactly match a version release
ifeq ($(MAKECMDGOALS), release)

  # Use the version number as the release tag.
  DOCKER_TAG = $(CODE_VERSION)
  ifndef CODE_VERSION
	$(error You need to create a VERSION file to build a release)
  endif

  # See what commit is tagged to match the version
  VERSION_COMMIT = $(shell git rev-list $(CODE_VERSION) -n 1 | cut -c1-7)
  ifneq ($(VERSION_COMMIT), $(GIT_COMMIT))
	$(error echo You are trying to push a build based on commit $(GIT_COMMIT) but the tagged release version is $(VERSION_COMMIT))
  endif

  # Don't push to Docker Hub if this isn't a clean repo
  ifneq (x$(GIT_NOT_CLEAN_CHECK), x)
	$(error echo You are trying to release a build based on a dirty repo)
  endif

else
  # Add the commit ref for development builds. Mark as dirty if the working directory isn't clean
  DOCKER_TAG = $(CODE_VERSION)-$(GIT_COMMIT)$(DOCKER_TAG_SUFFIX)
endif

# Build the Docker container:
docker_build:
	# Build Docker image
	docker build \
		--build-arg BUILD_DATE=`date -u +"%Y-%m-%dT%H:%M:%SZ"` \
		--build-arg VERSION=$(CODE_VERSION) \
		--build-arg VCS_URL=`git config --get remote.origin.url` \
		--build-arg VCS_REF=$(GIT_COMMIT) \
		-t $(DOCKER_IMAGE):$(DOCKER_TAG) .

# Run the Docker container:
docker_run:
	docker run -it \
	    -p 8888:80 \
	    -e "MYSQL_ADMIN_PASS=changeme" \
		-e "MYSQL_USER_PASS=changeme" \
		-v `pwd`/meshmap-mysql:/var/lib/mysql \
		-v `pwd`/user-settings.ini:/meshmap/scripts/user-settings.ini \
		$(DOCKER_IMAGE)

# Push the Docker container to Docker Hub (for local builds):
docker_push:
	# Tag image as latest
	docker tag $(DOCKER_IMAGE):$(DOCKER_TAG) $(DOCKER_IMAGE):latest

	# Push to DockerHub
	docker push $(DOCKER_IMAGE):$(DOCKER_TAG)
	docker push $(DOCKER_IMAGE):latest

output:
	@echo Docker Image: $(DOCKER_IMAGE):$(DOCKER_TAG)

date:
	@echo $(shell date)
