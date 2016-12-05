#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install php-sqlite3
mysql -uroot -proot << EOF
    USE mysql;
    UPDATE user SET password = Password('') WHERE User='root';
    FLUSH PRIVILEGES;
EOF
