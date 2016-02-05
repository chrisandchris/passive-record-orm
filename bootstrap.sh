#!/usr/bin/env bash

sudo apt-get update
sudo apt-get install php5-sqlite
mysql -uroot -p << EOF
    USE mysql;
    UPDATE user SET password = Password('') WHERE User='root';
    FLUSH PRIVILEGES;
EOF
