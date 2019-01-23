# flysystem-backblaze

[![Author](http://img.shields.io/badge/author-@mhetreramesh-blue.svg?style=flat-square)](https://twitter.com/mhetreramesh)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/mhetreramesh/flysystem-backblaze.svg?style=flat-square)](https://packagist.org/packages/mhetreramesh/flysystem-backblaze)
[![Software License][ico-license]](LICENSE.md)
[![Build Status](https://img.shields.io/travis/gliterd/flysystem-backblaze/master.svg?style=flat-square)](https://travis-ci.org/gliterd/flysystem-backblaze)
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads](https://img.shields.io/packagist/dt/mhetreramesh/flysystem-backblaze.svg?style=flat-square)](https://packagist.org/packages/mhetreramesh/flysystem-backblaze)

Visit (https://secure.backblaze.com/b2_buckets.htm) and get your account id, application key.

The Backblaze adapter gives the possibility to use the Flysystem filesystem abstraction library with backblaze. It uses the [Backblaze B2 SDK](https://github.com/cwhite92/b2-sdk-php) to communicate with the API.

## Install

Via Composer

``` bash
$ composer require mhetreramesh/flysystem-backblaze
```

## Usage

``` php
use Mhetreramesh\Flysystem\BackblazeAdapter;
use League\Flysystem\Filesystem;
use BackblazeB2\Client;

$client = new Client($accountId, $applicationKey);
$adapter = new BackblazeAdapter($client,$bucketName);

$filesystem = new Filesystem($adapter);
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mhetreramesh@gmail.com instead of using the issue tracker.

## Credits

- [Ramesh Mhetre][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/mhetreramesh/flysystem-backblaze.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/gliterd/flysystem-backblaze/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/gliterd/flysystem-backblaze.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/gliterd/flysystem-backblaze.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/mhetreramesh/flysystem-backblaze.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/mhetreramesh/flysystem-backblaze
[link-travis]: https://travis-ci.org/gliterd/flysystem-backblaze
[link-scrutinizer]: https://scrutinizer-ci.com/g/gliterd/flysystem-backblaze/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/gliterd/flysystem-backblaze
[link-downloads]: https://packagist.org/packages/mhetreramesh/flysystem-backblaze
[link-author]: https://github.com/mhetreramesh
[link-contributors]: ../../contributors
