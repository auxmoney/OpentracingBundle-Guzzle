#!/bin/bash

cd build/testproject
symfony local:server:stop
cd ../../
rm -fr build/testproject
docker rm jaeger
exit 0
