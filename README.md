# CORS

[![Software License](https://img.shields.io/badge/license-Apache_2.0-brightgreen.svg?style=flat-square)](LICENSE.md)

Provides CORS functionality for PSR-7 compatible frameworks, and is easily extensible for use in practically any other framework.

Officially supported frameworks include:

* Slim 3

However this package includes a service provider which can be used by any [container-interop](https://github.com/container-interop/container-interop) service container implementing "delegate lookup".

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Cors:

```bash
$ composer require phpnexus/cors
```

This package requires PHP 5.5.9 or newer.

## Usage

### Slim 3

The Slim 3 framework uses PSR-7 HTTP messages and a container-interop compatible implementation of the Pimple service container.

To enable CORS support in your application, follow these steps:

#### Add the CORS service provider to your service container

*dependencies.php*

```php
$container->register(new PhpNexus\Cors\Providers\Slim3ServiceProvider);
```

#### Add the CORS PSR-7 middleware to your app

*middleware.php*

```php
$app->add(new CorsPsr7Middleware());
```

#### Add your configuration

*settings.php*

```php
$settings['cors'] = [
    'allow-methods'     => ['GET', 'POST'],
    'allow-headers'     => ['Content-Type'],
    'allow-origins'     => [],
    'allow-credentials' => false,
    'expose-headers'    => [],
    'max-age'           => 0,
];
```

### Other frameworks

The `CorsService` class can be used directly with any other frameworks.

TODO

## Roadmap

* Official support for Zend Expressive
 * Support for Zend\ServiceManager and Aura.Di containers
 * Documentation
* Support for other containers NOT implementing "delegate lookup"

## Versioning

The packages adheres to the [SemVer](http://semver.org/) specification, and there will be full backward compatibility between minor versions.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

This package is released under the Apache 2.0 License. See the bundled [LICENSE](https://github.com/phpnexus/cors/blob/master/LICENSE) file for details.
