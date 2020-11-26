# Litermi Logger TCP or UDP

A package to send logs to a server via udp or tcp.

It uses the new [Laravel custom log channel](https://laravel.com/docs/master/logging) introduced in Laravel 5.6.

## Table of contents

- [Table of contents](#table-of-contents)
- [Installation](#installation)
- [Usage](#usage)
  - [Example](#example)
- [License](#license)

## Installation

Install via [composer](https://getcomposer.org/doc/00-intro.md)

```sh
composer require litermi/elasticlog:dev-master
```

add row in config/app.php

```php
 \Litermi\Elasticlog\ElasticServiceProvider::class
```


publish config

```sh
php artisan vendor:publish --provider="Litermi\Elasticlog\Providers\ElasticServiceProvider"
```


Edit `config/logging.php` to add the new `logger` logs channel.

```php
return [
        'tcp-logger'   => [
            'name'   => 'elastic',
            'driver' => 'custom',
            'via'    => \Litermi\Elasticlog\Services\Logs\SendLogTcpMonolog::class,
        ],
        'udp-logger'     => [
            'driver'  => 'monolog',
            'handler' => \Litermi\Elasticlog\Services\Logs\SendLogUdpJsonHandler::class,
        ],
        'stderr' => [
            'driver'    => 'monolog',
            'handler'   => \Monolog\Handler\StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with'      => [
                'stream' => 'php://stderr',
            ],
        ],
];
```

## Usage

Once you have modified the Laravel logging configuration, you can send log channel via tcp or udp [as any Laravel log channel](https://laravel.com/docs/master/logging#writing-log-messages).

### Example

```.env
LOG_CHANNEL=tcp-logger
or
LOG_CHANNEL=udp-logger
```

### UPDATE PACKAGE IN PROJECTS
```bash
composer update "litermi/elasticlog"
```

## License

litermi elastic is released under the MIT Licence. See the bundled [LICENSE](https://github.com/litermi/elasticlog/blob/master/LICENSE.md) file for details.
