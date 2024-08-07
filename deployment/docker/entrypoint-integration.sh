#!/bin/bash
set -ex

if [ ! -d "/app/public/data/" ]; then
    mkdir /app/public/data/
fi

cd /app
/entrypoint.sh &

export PLAYWRIGHT_API_URL=http://e2e-testrunner:3000
export SYSTEM_UNDER_TEST_URL_FOR_PLAYWRIGHT=http://$(hostname -i):9090

echo "####### Waiting for system under test to start"

# We use a counter here so that we can stop the pipeline and not have it run
# forever.
counter=0
until $(curl --output /dev/null --silent --head --fail http://127.0.0.1:9090); do
    if [ "$counter" -gt 25 ]; then
        echo "FAILED: Waiting for system under test to start took too long!!!"
        exit 1
    fi
    echo "."
    counter=$((counter+1))
    sleep 2
done

echo "------> SUCCESS starting system under test"

echo "####### Running Unit Tests"
cd /app && vendor/bin/phpunit --colors=always
echo "------> SUCCESS finished running Unit tests"

echo "####### Running integration Tests"
cd /app && vendor/bin/behat --format pretty --out std --tags integration
echo "------> SUCCESS finished running integration tests"

echo "ALL DONE"
