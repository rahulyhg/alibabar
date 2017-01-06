# alibabar

Simple Silex application to showcase several workshops on Aliyun.

## dev setup
```
# build image
docker/main.sh

# add DNS record into /etc/hosts
# 127.0.0.1 alibabar

# start project
# - WWW_USER and WWW_UID are used to work around permission issues related to 
# sharing volumes; WWW_USER should be the user name of the directory owner and WWW_UID the UID of that user
# - DEV used to set some dev parameters and enable extensions
WWW_USER=`ls -l build.sh | awk '{print $4}'`
WWW_UID=`ls -n build.sh | awk '{print $4}'`
docker run \
    -d -p 80:80 \
    --env DNS=alibabar \
    --env WWW_USER=$WWW_USER \
    --env WWW_UID=$WWW_UID \
    --env DEV=1 \
    --net=host \
    -v `pwd`:/var/www/hosts/alibabar \
    alibabar
    
# start mysql
# start redis
```