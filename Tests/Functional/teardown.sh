#!/bin/bash

cd build/testproject
symfony local:server:stop
cd ../../
rm -fr build/testproject
sudo systemctl stop php-fpm
exit 0
