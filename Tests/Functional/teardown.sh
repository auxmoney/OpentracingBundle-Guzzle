#!/bin/bash

export PATH="$HOME/.symfony/bin:$PATH"
cd build/testproject
symfony local:server:stop
cd ../../
rm -fr build/testproject
docker stop jaeger
