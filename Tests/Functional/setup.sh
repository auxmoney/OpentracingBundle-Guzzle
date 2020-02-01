#!/bin/bash

wget https://get.symfony.com/cli/installer -O - | bash
sudo mv /home/travis/.symfony/bin/symfony /usr/local/bin/symfony
docker run -d --rm --name jaeger \
  -e COLLECTOR_ZIPKIN_HTTP_PORT=9411 \
  -p 5775:5775/udp \
  -p 6831:6831/udp \
  -p 6832:6832/udp \
  -p 5778:5778 \
  -p 16686:16686 \
  -p 14268:14268 \
  -p 14250:14250 \
  -p 9411:9411 \
  jaegertracing/all-in-one:1.16
./wait-for-it.sh localhost:16686
sleep 3
mkdir build/
ORIGIN_DIR=`pwd`
BRANCH=${TRAVIS_PULL_REQUEST_BRANCH:-$TRAVIS_BRANCH}
cd build/
symfony new --version=stable --no-git testproject
cd testproject
composer config minimum-stability dev # TODO: remove as soon as all dependencies  (opentracing, jaeger-php) are released as stable version
composer config prefer-stable true    # TODO: remove as soon as all dependencies  (opentracing, jaeger-php) are released as stable version
composer require auxmoney/opentracing-bundle-jaeger auxmoney/opentracing-bundle-guzzle:dev-${BRANCH}
yes | cp -rf ${ORIGIN_DIR}/Tests/Functional/TestProjectFiles/* .
composer dump-autoload
symfony console cache:clear
symfony local:server:start -d --no-tls
cd ${ORIGIN_DIR}
