#!/bin/bash
set -euox pipefail

PROJECT=${PROJECT:-"alibabar"}
ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )"/.. && pwd )"
HOST_ROOT=${HOST_ROOT:-"$ROOT"}
TAG=${TAG:-"latest"};

# build specific
COMPOSER_NO_DEV=${COMPOSER_NO_DEV:-'--no-dev'}

# set directory
DIR=$ROOT;
PWD=`pwd`
cd $ROOT

# build the build container
docker build -t $PROJECT-build:$TAG -f $ROOT/docker/build/Dockerfile $ROOT

# execute the build container to generate the app
docker run \
    --rm \
    -v $HOST_ROOT:/opt/$PROJECT \
    -v `pwd`/tmp:/compiled \
    -e PROJECT=$PROJECT \
    -e COMPOSER_NO_DEV=$COMPOSER_NO_DEV \
    $PROJECT-build:$TAG

if [[ `uname` == 'Darwin' ]]; then
    tar --exclude '.*' --exclude 'project.tgz' -c -z -f project.tgz ./*
else
    tar -c -z --exclude='.[^/]*' --exclude='project.tgz' -f project.tgz ./*
fi

# build the distribution container
docker build -t $PROJECT:$TAG -f $ROOT/docker/dist/Dockerfile $ROOT

# remove archive
rm project.tgz

cd $PWD