# Flysystem Adapter for Dropbox

[![Author](http://img.shields.io/badge/author-@frankdejonge-blue.svg?style=flat-square)](https://twitter.com/frankdejonge)
[![Build Status](https://img.shields.io/travis/thephpleague/flysystem-dropbox/master.svg?style=flat-square)](https://travis-ci.org/thephpleague/flysystem-dropbox)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/thephpleague/flysystem-dropbox.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-dropbox/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/thephpleague/flysystem-dropbox.svg?style=flat-square)](https://scrutinizer-ci.com/g/thephpleague/flysystem-dropbox)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/league/flysystem-dropbox.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-dropbox)
[![Total Downloads](https://img.shields.io/packagist/dt/league/flysystem-dropbox.svg?style=flat-square)](https://packagist.org/packages/league/flysystem-dropbox)


## Installation

```bash
composer require league/flysystem-dropbox
```

## Usage

Visit https://www.dropbox.com/developers/apps and get your "App secret".

You can also generate OAuth access token for testing using the Dropbox App Console without going through the authorization flow.

~~~ php
use League\Flysystem\Dropbox\DropboxAdapter;
use League\Flysystem\Filesystem;
use Dropbox\Client;

$client = new Client($accessToken, $appSecret);
$adapter = new DropboxAdapter($client, [$prefix]);

$filesystem = new Filesystem($adapter);
~~~
