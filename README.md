# ![Laravel RealWorld Example App](.github/readme/logo.png)

[![Tests: status](https://github.com/f1amy/laravel-realworld-example-app/actions/workflows/tests.yml/badge.svg)](https://github.com/f1amy/laravel-realworld-example-app/actions/workflows/tests.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

> Example of a PHP-based Laravel application containing real world examples (CRUD, auth, advanced patterns, etc) that adheres to the [RealWorld](https://github.com/gothinkster/realworld) API spec.

This codebase was created to demonstrate a backend application built with [Laravel framework](https://laravel.com/) including RESTful services, CRUD operations, authentication, routing, pagination, and more.

We've gone to great lengths to adhere to the Laravel framework community style guides & best practices.

For more information on how to this works with other frontends/backends, head over to the [RealWorld](https://github.com/gothinkster/realworld) repo.

## How it works

standard laravel application, utilizing framework features

custom jwt implementation

## Getting started

The preferred way of setting up the project is using [Laravel Sail](https://laravel.com/docs/sail), \
for that you'll need [Docker](https://docs.docker.com/get-docker/) under Linux / macOS (or Windows WSL2).

### Installation

Clone the repository:

    git clone https://github.com/f1amy/laravel-realworld-example-app.git
    cd laravel-realworld-example-app

Install dependencies (if you have `composer`):

    composer create-project

Alternatively you can do the same with Docker:

    docker run --rm -it \
        --volume $PWD:/app \
        --user $(id -u):$(id -g) \
        composer create-project

Start the containers with PHP application and PostgreSQL database:

    ./vendor/bin/sail up -d

(Optional) Configure a Bash alias for `sail` command:

    alias sail='[ -f sail ] && bash sail || bash vendor/bin/sail'

Migrate the database with seeding:

    sail artisan migrate --seed

## Usage

The API is available at `http://localhost:3000/api` (You can change the `APP_PORT` in `.env` file).

### Run Tests

    sail artisan test

### Run PHPStan static analysis

    sail php ./vendor/bin/phpstan

### OpenAPI Specification (underway)

Swagger UI will be live at [http://localhost:3000/api/documentation](http://localhost:3000/api/documentation).

## Contributions

Feedback, suggestions, and improvements are welcome, feel free to contribute.
