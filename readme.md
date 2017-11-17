# Laravel Bitnami EMP

A bare-bones laravel install using the [Bitnami EMP docker config](https://docs.bitnami.com/containers/how-to/create-emp-environment-containers/).

## Quickstart

In the project root, start everything with `docker-compose up -d`. This will start the containers in 'daemon' mode so it doesn't clutter up your terminal with log output.

Run the following commands to install dependencies, set up your `.env` file properly, set app key etc:

```bash
bin/composer install
cp -n .env.example .env
bin/artisan key:generate
```

The running application will be available at http://localhost:3080/ (you can change this port in `docker-compose.yml`)

## docker-compose.yml

Uses a simple three-container approach for serving the laravel app:

* MariaDB (a MySQL compatible DB) with a local volume for data persistence
* Nginx with a customizable vhost config file, which reverse-proxies php requests to PHP-FPM container
* PHP-FPM running PHP 7.1

Also includes utilities useful for development and debugging (more info below for each)

* `redis-commander`, a web UI for Redis
* `mailcatcher` a mock email server that you can send as many emails as you like, with a web interface to read them

To view logs, you can use `docker-compose logs`, read more about logging here: https://docs.docker.com/compose/reference/logs/

## `docker/` folder

* Contains a `data` directory that the MariaDB container will use for persisting its data.
* Has a customizable `nginx-vhost/laravel.nginx` file, which listens on all interfaces at port 8080 and serves the laravel app.
    * the `location /` section will first try loading a file directly, which will work for any static files (css, js, etc)
    * if it doesn't match, then it passes along the request to index.php
    * the next `location ~ \.php$` section handles an incoming php request (such as when the previous section doesn't find a matching file)
    * this passes along the request to the php-fpm container, which has a pool listening on port 9000

## `bin/` folder

Working within the containers can be tedious, always having to type `docker-compose exec...` commands sucks.

To make this easier, there are helper scripts in the `bin/` folder that pass through whatever args you give, and execute the commands within the docker containers.

* `composer`: runs php composer commands (eg. `bin/composer install`, `bin/composer update` etc)
* `artisan`: runs the laravel artisan command (eg. `bin/artisan migrate`)
* `phpunit`: runs PHPUnit tests (eg. `bin/phpunit` will run the default test suite)
    
## Laravel

### `.env.example`

The example `.env` file has been configured to connect to mariadb based on the values in docker-compose.yml

### Redis

To support redis for cache/sessions etc, the composer `predis/predis` package has been installed.

### Index length fixes

In order to support older versions of MariaDB, this is added to the `AppServiceProvider::boot()` method:

```php
// fix issues with utf8mb4 and older versions of mariadb
Schema::defaultStringLength(191);
```

More info here in the Laravel documentation, under the "Index Lengths & MySQL / MariaDB" section:
https://laravel.com/docs/5.5/migrations#creating-indexes

### Sending Emails

An example of how to create a Mailable class, view and Unit test is included:

* `app/Mail/HelloWorld.php` demonstrates a Mailable class
* `resources/views/email/hello-world.blade.php` shows how to write an email template consuming data from the Mailable class
* `tests/Unit/MailTest.php` shows a PHPUnit test that sends a test email and asserts no errors.

## Redis and Redis-Commander

Redis is a really fast in-memory key/value store that is perfect for caching and storing user sessions. Laravel supports it really well out of the box.

Looking at the data can be useful but is hard without a GUI client, so a simple web GUI, [redis-commander](http://joeferner.github.io/redis-commander/) is included.

* Access the redis-commander web UI at http://localhost:8091 to explore your redis cache 

## Mailcatcher

Debugging email can be a pain, so [mailcatcher](https://mailcatcher.me/) is included as well. This provides a simple SMTP server that will intercept emails sent to any address from Laravel, and store them for display in a nice web UI.

* Access the mailcatcher web UI at http://localhost:1081 to view the emails
* You can also test emailing from your host, by setting up an "SMTP server" using host: `localhost`, port: `1025`