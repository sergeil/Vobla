#!/bin/bash

cd ../
git submodule update --init

cd src/vendor/Doctrine
git checkout 3.0.x
git pull

echo
echo
echo --------------------------------------------------------
echo Submodules were successfully initialized but to make
echo it sure that everything is fine go to /tests/ directory
echo and execute \"phpunit .\"
echo --------------------------------------------------------