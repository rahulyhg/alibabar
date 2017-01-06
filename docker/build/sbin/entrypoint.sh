#!/bin/bash
set -euo pipefail
cd /opt/$PROJECT
./build.sh

# copy extensions build for php
cp /usr/local/lib/php/extensions/no-debug-non-zts-20151012/* /compiled