# alibabar

Simple Silex application to showcase several workshops on Aliyun.

## dev setup
```
# build image
docker/main.sh

# add DNS record into /etc/hosts
# 127.0.0.1 alibabar

# redis
docker run -d \
    --net=host \
    -p 6379:6379 \
    redis:3.2-alpine
    
# mysql
docker run -d \
    --net=host \
    -e MYSQL_ROOT_PASSWORD=root \
    -e MYSQL_DATABASE=alibabar \
    -p 3306:3306 \
    mysql:5.6

# start project
# - WWW_USER and WWW_UID are used to work around permission issues related to 
# sharing volumes; WWW_USER should be the user name of the directory owner and WWW_UID the UID of that user
# - DEV used to set some dev parameters and enable extensions
# - other variables can be found in the "env" file; replace it with your own config as necessary
WWW_USER=`ls -l build.sh | awk '{print $4}'`
WWW_UID=`ls -n build.sh | awk '{print $4}'`
id=$(docker run -d \
    --net=host \
    -p 80:80 \
    --env WWW_USER=$WWW_USER \
    --env WWW_UID=$WWW_UID \
    --env-file `pwd`/env \
    -v `pwd`:/var/www/hosts/alibabar \
    alibabar)
    
# run the migrations and seed
docker exec $id /var/www/hosts/alibabar/vendor/bin/phinx migrate -c /var/www/hosts/alibabar/phinx.yml
docker exec $id /var/www/hosts/alibabar/vendor/bin/phinx seed:run -c /var/www/hosts/alibabar/phinx.yml
```