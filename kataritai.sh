#!/bin/bash

service php-fpm restart
echo "" > /var/log/nginx/access.log
/home/isucon/benchmarker bench
cat /var/log/nginx/access.log | /usr/local/bin/kataribe -f /opt/kataribe.toml
