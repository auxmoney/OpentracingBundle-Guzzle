#!/bin/bash

sudo netstat -nltp
cd build/testproject
symfony local:server:stop
cd ../../
rm -fr build/testproject
sudo netstat -nltp
