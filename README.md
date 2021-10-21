# Petersons D2L API client

[![Build Status](https://github.com/petersons/d2l-client/workflows/Tests/badge.svg?branch=main)](https://github.com/petersons/d2l-client/actions)
[![codecov](https://codecov.io/gh/petersons/d2l-client/branch/main/graph/badge.svg?token=CVOQ23H1GE)](https://codecov.io/gh/petersons/d2l-client)

## Status

This package is currently in active development.

## Installation
1. Require the package using Composer:

```sh
composer require petersons/d2l-client
```

## Features

* Provides a PHP HTTP client to communicate with D2L APIs
* Provides integration with Laravel

## Requirements

* [PHP 8.0](https://www.php.net/releases/8_0_0.php) or greater

## Usage example

```php
use Petersons\D2L\AuthenticatedUriFactory;
use Petersons\D2L\DTO\Enrollment\CreateEnrollment;
use Petersons\D2L\SymfonyHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;

$client = new SymfonyHttpClient(
            ScopingHttpClient::forBaseUri(
                HttpClient::create(),
                'https://petersonstest.brightspace.com',
            ),
            new AuthenticatedUriFactory(
                'https://petersonstest.brightspace.com',
                'appId',
                'appKey',
                'lmsUserId',
                'lmsUserKey',
            ),
            'orgId',
            'installationCode',
            'pKey',
            'apiLpVersion', // e.g. 1.30
            'apiLeVersion', // e.g. 1.53
        );

$client->enrollUser(new CreateEnrollment(1, 2, 3));
```

## Laravel integration

You may publish the configuration file using the vendor:publish Artisan command:

```bash
php artisan vendor:publish --tag=d2l-config
```

After setting up all the needed config env variables you can typehint
the `\Petersons\D2L\Contracts\ClientInterface` interface via the constructor of your service and start using it.

## Local development

Docker dependencies for local development:
- [Docker Engine](https://docs.docker.com/engine/) >= 19.03
- [Docker Compose](https://docs.docker.com/compose/) >= 1.25.5

0. Clone project
    ```bash
    git clone git@gitlab.com:petersons/d2l-client.git
    ```

0. Build the Docker image
    ```bash
    dev/bin/docker-compose build --build-arg PHP_VERSION=8.0 php
    ```

0. Install library dependencies
    ```bash
    dev/bin/php composer update
    ```

0. Running all tests with Xdebug debugging disabled
    ```bash
    dev/bin/php-test vendor/bin/phpunit
    ```

0. Running all tests with Xdebug debugging enabled
    ```bash
    dev/bin/php-debug vendor/bin/phpunit
    ```

0. Running linter
    ```bash
    dev/bin/php-test vendor/bin/php-cs-fixer fix
    ```

0. Clear Docker volumes
    ```bash
    dev/bin/docker-compose down --volumes
    ```
