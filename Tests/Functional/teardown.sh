#!/bin/bash

cd build/testproject
symfony local:server:stop
symfony local:server:list
ps aux | grep php
ps aux | grep symfony
ps aux | grep docker
cd ../../
rm -fr build/testproject
return 0
