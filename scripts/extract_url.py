#!/usr/bin/env python

import argparse
import os

from urllib.parse import urlparse

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
