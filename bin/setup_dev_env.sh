#! /usr/bin/env bash
set -e
SCRIPT_DIR=$(dirname $(readlink -fn ${BASH_SOURCE[0]}))

cd $SCRIPT_DIR/..

#composer install
mkdir -p var/log
