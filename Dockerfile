FROM php:8.3-cli
WORKDIR /app
COPY . /app/
ENV PHP_CLI_SERVER_WORKERS=4
EXPOSE 8080
CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t public public/index.php"]
