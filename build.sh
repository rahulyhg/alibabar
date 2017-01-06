#!/bin/bash
set -eoux pipefail

# config
project=`pwd`
build_dir=$project/build
web_dir=$build_dir/web
assets_dir=$project/assets
app_file=$web_dir/app.php
commit=`git rev-parse --short HEAD`

branch=`git rev-parse --abbrev-ref HEAD`

composer install $COMPOSER_NO_DEV -v
composer dump-autoload -o

# clean up build dir
rm -Rf $build_dir;
mkdir -p $web_dir;
cd $web_dir;

# copy assets
mkdir -p $web_dir/$commit
rsync -rtuz $assets_dir/* $web_dir/$commit

echo "<?php
putenv('COMMIT=$commit');
require_once '../../vendor/autoload.php';
require_once '../../config/local.php.dist';
require_once '../../src/init/app.php';
require_once '../../src/init/di.php';
require_once '../../src/init/routing.php';
" > $app_file;
