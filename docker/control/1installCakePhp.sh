#!/bin/sh
cd /var/www/vhosts/website.com/www
svn export https://github.com/cakephp/cakephp/branches/2.x
mv 2.x src 

#fix the permissions
chmod -R 777 src/app/tmp/