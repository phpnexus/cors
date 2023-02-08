# CORS

[![Latest Version](http://img.shields.io/packagist/v/phpnexus/cors.svg?style=flat-square)](https://github.com/phpnexus/cors/releases)
[![Build Status](https://img.shields.io/travis/phpnexus/cors/master.svg?style=flat-square)](https://travis-ci.org/phpnexus/cors)
[![Software License](https://img.shields.io/badge/license-Apache_2.0-brightgreen.svg?style=flat-square)](LICENSE.md)

Provides a lightweight, extensible, framework-agnostic CORS class.

**You probably want to check these specific implementations for easy installation**

* [PSR-7](https://github.com/phpnexus/cors-psr7)

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install Cors:

```bash
$ composer require phpnexus/cors
```

This package requires PHP 7.4 or 8.1.

## Usage

TODO

## Configuration

### Allow-Methods

Default: `[]`

An array of allowed HTTP methods. These names are **case-sensitive**.

Example: `['GET', 'POST']`

### Allow-Headers

Default: `[]`

Example: `['Content-Type']`

### Allow-Origins

Default: `[]`

An array of allowed origins, in the form `scheme://hostname`.

Example: `['http://example.com', 'https://example.com']`

**This is not a replacement for proper access control measures.**

Note: An asterisk (`*`) _can_ also be used to allow any origin, but as per the specification the asterisk (`*`) _cannot_ be used when Allow-Credentials is `true`.

### Allow-Credentials

Default: `false`

Use `true` to allow cookies to be sent with the request.

Note: Cannot be `true` when the Allow-Origin contains `"*"`.

### Expose-Headers

Default: `[]`

### Max-Age

Default: `0` (no cache)

Number of seconds to cache the preflight response.

## Roadmap

* Benchmarks

## Versioning

The packages adheres to the [SemVer](http://semver.org/) specification, and there will be full backward compatibility between minor versions.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

This package is released under the Apache 2.0 License. See the bundled [LICENSE](https://github.com/phpnexus/cors/blob/master/LICENSE) file for details.
