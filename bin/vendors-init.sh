#!/bin/bash

cd ../
git submodule update --init

cd lib/vendor/Doctrine
git checkout 3.0.x
git pull

cd ../Moko
git checkout master
git pull

echo
echo
echo --------------------------------------------------------
echo Submodules were successfully initialized but to make
echo it sure that everything is fine go to /tests/ directory
echo and execute \"phpunit .\"
echo --------------------------------------------------------