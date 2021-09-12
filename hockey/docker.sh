docker run -d -it \
    -v "$PWD":/var/www/html/:rw \
    --name hockeyApp --hostname hockeyApp \
    -w /var/www/html \
    -p 80:80 \
    php:7.2-apache