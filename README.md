# URouter

A simple trie-based PHP router

## Installation

Via composer:

```sh
composer require em4nl/urouter
```

## Usage

Assuming you're using autoloading and your composer vendor dir is
at `./vendor`:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

$router = new Em4nl\U\Router();

// set a base path if this app doesn't live at the root path
// (right behind the domain)
$router->base('/my-app');

$router->get('/', function($context) {
    echo 'the index route';
});

$router->get('/test', function($context) {
    echo "the {$context['path']} route";
});

$router->get('/:thing', function($context) {
    echo "I like {$context['params']['thing']}!";
});

$router->get('/test/*', function($context) {
    // will match paths of arbitrary length behind /test/ ...
});

$router->post('/form', function($context) {
    // ...
});

$router->catchall(function($context) {
    header('HTTP/1.1 404 Not Found');
    // ...
});

$router->run();
```

Routes don't have to be defined in any particular order, they will
be matched by specificity automatically. E.g. if you visit
`/test/`, the `/test` route will match and not the `/:thing` route,
even if the latter would be defined earlier in the source code.

## Development

Install dependencies

```sh
composer install
```

Run tests

```sh
./vendor/bin/phpunit tests
```

## License

[The MIT License](https://github.com/em4nl/wpinstall/blob/master/LICENSE)
