# ampledata/meshmap-docker

Docker container to run [Eric Satterlee KG6WXC's AREDN MeshMap](https://gitlab.kg6wxc.net/mesh/meshmap/tree/master).

## Usage

1. Download a copy of [user-settings.ini-default](https://gitlab.kg6wxc.net/mesh/meshmap/blob/master/scripts/user-settings.ini-default) and rename it to `user-settings.ini`:
    ```bash
   $ curl -O user-settings.ini https://gitlab.kg6wxc.net/mesh/meshmap/raw/master/scripts/user-settings.ini-default
   ```
2. Configure the `user-settings.ini` per instructions [here](https://gitlab.kg6wxc.net/mesh/meshmap/blob/master/scripts/user-settings.ini-default).
    At a minimum you will need to set `sql_passwd`.
3. Create a mysql database directory for persistence: `$ mkdir meshmap-mysql`
4. Start the Docker container, ensure you use the same `sql_passwd` above for `MYSQL_USER_PASS` below:
    ```bash
    $ docker run -d \
        -p 8888:80 \
        -e "MYSQL_ADMIN_PASS='changeme'" \
        -e "MYSQL_USER_PASS='changeme'" \
        -v `pwd`/meshmap-mysql:/var/lib/mysql \
        -v `pwd`/user-settings.ini:/meshmap/scripts/user-settings.ini \
        ampledata/meshmap
    ```
5. Open a web browser and point it at http://localhost:8888

## Environment

The following environment variables must be overwritten before running this container. This can be accomplished by 
using the `-e` command-line argument, or by modifying the Dockerfile (no, don't do it!).

* `MYSQL_ADMIN_PASS`: Sets the mysql administrator password.
* `MYSQL_USER_PASS`: Sets the mysql user `mesh-map` password.

Setting the environment variable from the command line:
```bash
-e "MYSQL_ADMIN_PASS='changeme'" -e "MYSQL_USER_PASS='changeme'"
```

Alternatively, setting the environment variables from the Dockerfile:
```dockerfile
ENV MYSQL_ADMIN_PASS=changeme
ENV MYSQL_USER_PASS=changeme
```

## Volumes

This container needs to persist its mysql database somewhere. It's recommended to use a Docker volume to do this. 
(Ensure the path exists first.)

Setting the mysql database volume from the command line:
```bash
-v `pwd`/meshmap-mysql:/var/lib/mysql
```
This command would set the container's local mysql database path to `meshmap-mysql` within your home directory.

## docker-compose

You can also run this container with Docker Compose, just remember to set the appropriate env vars:
```yaml
version: "2"
volumes:
  meshmap-mysql:
services:
  meshmap:
    image: meshmap-docker
    hostname: meshmap
    restart: always
    ports:
      - "8888:80"
    volumes:
      - "meshmap-mysql:/var/lib/mysql"
    environment:
        MYSQL_ADMIN_PASS: "changeme"
        MYSQL_USER_PASS: "changeme"
```

Once the instance is running, all you have to do is open a web browser and point it to `http://localhost:8888`

## License
