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

$router->get('/', function($context) {
    echo 'the index route';
});

$router->get('/test', function($context) {
    echo "the {$context['path']} route";
});

$router->get('/:thing', function($context) {
    echo "I like {$context['params']['thing']}!";
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

## License

[The MIT License](https://github.com/em4nl/wpinstall/blob/master/LICENSE)
