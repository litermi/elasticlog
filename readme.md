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
composer require litermi/elasticlog
```

add row in config/app.php

```php
 \Cirelramostrabajo\Plogger\ElasticServiceProvider::class
```


publish config

```sh
php artisan vendor:publish --provider="Cirelramostrabajo\Plogger\Providers\ElasticServiceProvider"
```


Edit `config/logging.php` to add the new `logger` logs channel.

```php
return [
        'tcp-logger'   => [
            'name'   => 'elastic',
            'driver' => 'custom',
            'via'    => \Cirelramostrabajo\Plogger\Services\Logs\SendLogTcpMonolog::class,
        ],
        'udp-logger'     => [
            'driver'  => 'monolog',
            'handler' => \Cirelramostrabajo\Plogger\Services\Logs\SendLogUdpJsonHandler::class,
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

## License

litermi elastic is released under the MIT Licence. See the bundled [LICENSE](https://github.com/cirelramostrabajo/plogger/blob/master/LICENSE.md) file for details.
