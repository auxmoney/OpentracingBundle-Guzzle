#!/bin/bash

cd build/testproject
symfony local:server:stop
symfony local:server:list
cd ../../
rm -fr build/testproject
exit 0
