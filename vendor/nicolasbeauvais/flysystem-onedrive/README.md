# Flysystem adapter for the Microsoft OneDrive API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nicolasbeauvais/flysystem-onedrive.svg?style=flat-square)](https://packagist.org/packages/nicolasbeauvais/flysystem-onedrive)
[![Build Status](https://img.shields.io/travis/nicolasbeauvais/flysystem-onedrive/master.svg?style=flat-square)](https://travis-ci.org/nicolasbeauvais/flysystem-onedrive)
[![StyleCI](https://styleci.io/repos/100028565/shield?branch=master)](https://styleci.io/repos/100028565)
[![Quality Score](https://img.shields.io/scrutinizer/g/nicolasbeauvais/flysystem-onedrive.svg?style=flat-square)](https://scrutinizer-ci.com/g/nicolasbeauvais/flysystem-onedrive)
[![Total Downloads](https://img.shields.io/packagist/dt/nicolasbeauvais/flysystem-onedrive.svg?style=flat-square)](https://packagist.org/packages/nicolasbeauvais/flysystem-onedrive)

This package contains a [Flysystem](https://flysystem.thephpleague.com/) adapter for OneDrive. Under the hood, the [Microsoft Graph SDK](https://github.com/microsoftgraph/msgraph-sdk-php) is used.

## Installation

You can install the package via composer:

``` bash
composer require nicolas-beauvais/flysystem-onedrive
```

## Usage

The first thing you need to do is get an authorization token for the Microsoft Graph API. For that you need to create an app on the [Microsoft App Registration Portal](https://apps.dev.microsoft.com/).

``` php
use Microsoft\Graph\Graph;
use League\Flysystem\Filesystem;
use NicolasBeauvais\FlysystemOneDrive\OneDriveAdapter;

$graph = new Graph();
$graph->setAccessToken('EwBIA8l6BAAU7p9QDpi...');

$adapter = new OneDriveAdapter($graph, 'root');
$filesystem = new Filesystem($adapter);

// Or to use the approot endpoint:
$adapter = new OneDriveAdapter($graph, 'special/approot');
```


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email nicolasbeauvais1@gmail.com instead of using the issue tracker.

## Credits

- [Nicolas Beauvais](https://github.com/nicolasbeauvais)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
