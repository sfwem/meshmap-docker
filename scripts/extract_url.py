#!/usr/bin/env python3
"""
Extracts components of a URL.

Developed for the San Francisco Wireless Emergency Mesh
    project: https://www.sfwem.net

Source:: https://github.com/sfwem/meshmap-docker
"""

import argparse
import os

from urllib.parse import urlparse

__author__ = 'Greg Albrecht W2GMD <oss@undef.net>'
__copyright__ = 'Copyright 2020 Greg Albrecht'
__license__ = 'Apache License, Version 2.0'


def main():
    parser = argparse.ArgumentParser(description='Process some integers.')
    parser.add_argument('-u', dest='username', action='store_true')
    parser.add_argument('-p', dest='password', action='store_true')
    parser.add_argument('-H', dest='host', action='store_true')
    parser.add_argument('-d', dest='database', action='store_true')
    args = parser.parse_args()

    db_uri = os.environ['CLEARDB_DATABASE_URL']
    result = urlparse(db_uri)
    user_password, host = result.netloc.split('@')
    user, password = user_password.split(':')
    path = result.path

    if args.username:
        print(user)
    if args.password:
        print(password)
    if args.host:
        print(host)
    if args.database:
        print(path.replace('/', ''))



if __name__ == '__main__':
    main()
