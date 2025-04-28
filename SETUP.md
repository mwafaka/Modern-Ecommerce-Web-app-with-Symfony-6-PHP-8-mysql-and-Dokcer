## Docker container Create

symfony-ecommerce/
├── docker/
│ ├── php/
│ │ └── Dockerfile
│ ├── nginx/
│ │ └── conf/
│ │ └── default.conf
├── docker-compose.yml
├── .env
└── README.md

1- Docker Setup

- docker/php/Dockerfile

```Dockerfile

FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libpq-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

```

2- docker-compose.yml

```yaml
services:
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    volumes:
      - .:/var/www/html
    ports:
      - "9000:9000"
    depends_on:
      - db

  nginx:
    image: nginx:alpine
    volumes:
      - .:/var/www/html
      - ./docker/nginx/conf:/etc/nginx/conf.d
    ports:
      - "8080:80"
    depends_on:
      - php

  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: ecommerce
    ports:
      - "3306:3306"
    volumes:
      - db_data:/var/lib/mysql

  adminer:
    image: adminer
    restart: always
    ports:
      - "8081:8080"

volumes:
  db_data:
```

3-docker/nginx/conf/default.conf

```nginx

server {
    listen 80;
    server_name localhost;

    root /var/www/html/public;

    index index.php;

    location / {
        try_files $uri /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}


```

## Symfony Setup

```bash

docker-compose up -d --build

docker exec -it symfony-ecommerce-php-1 bash

composer create-project symfony/skeleton ecommerce-app

cd ecommerce-app

composer require webapp
composer require doctrine orm maker security annotations twig

```
