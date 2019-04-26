FROM composer as builder

WORKDIR /app
ADD composer.json /app/
ADD composer.lock /app/
RUN composer install --no-dev
ADD . /app


FROM php:7.3.2-cli-stretch

RUN apt update
RUN apt install mysql-client -y
RUN docker-php-ext-install pdo_mysql

WORKDIR /app
COPY --from=builder /app /app
RUN echo 'php /app/bin/snakedumper $@' > /bin/snakedumper
RUN chmod +x /bin/snakedumper

CMD ["snakedumper"]
