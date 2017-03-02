## Backblaze B2 SDK for PHP
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Latest Version](https://img.shields.io/github/release/cwhite92/b2-sdk-php.svg?style=flat-square)](https://github.com/cwhite92/b2-sdk-php/releases)
[![SensioLabs Rating](https://img.shields.io/sensiolabs/i/d5e44d75-84d2-40c7-b0d4-7f628429e139.svg?style=flat-square)](https://insight.sensiolabs.com/projects/d5e44d75-84d2-40c7-b0d4-7f628429e139)
[![Build Status](https://img.shields.io/travis/cwhite92/b2-sdk-php.svg?style=flat-square)](https://travis-ci.org/cwhite92/b2-sdk-php)

`b2-sdk-php` is a client library for working with Backblaze's B2 storage service. It aims to make using the service as
easy as possible by exposing a clear API and taking influence from other SDKs that you may be familiar with.

## Example

This is just a short example, full examples to come on the wiki.

```php
use ChrisWhite\B2\Client;
use ChrisWhite\B2\Bucket;

$client = new Client('accountId', 'applicationKey');

// Returns a Bucket object.
$bucket = $client->createBucket([
    'BucketName' => 'my-special-bucket',
    'BucketType' => Bucket::TYPE_PRIVATE // or TYPE_PUBLIC
]);

// Change the bucket to private. Also returns a Bucket object.
$updatedBucket = $client->updateBucket([
    'BucketId' => $bucket->getId(),
    'BucketType' => Bucket::TYPE_PUBLIC
]);

// Retrieve an array of Bucket objects on your account.
$buckets = $client->listBuckets();

// Delete a bucket.
$client->deleteBucket([
    'BucketId' => '4c2b957661da9c825f465e1b'
]);

// Upload a file to a bucket. Returns a File object.
$file = $client->upload([
    'BucketName' => 'my-special-bucket',
    'FileName' => 'path/to/upload/to',
    'Body' => 'I am the file content'

    // The file content can also be provided via a resource.
    // 'Body' => fopen('/path/to/input', 'r')
]);

// Download a file from a bucket. Returns the file content.
$fileContent = $client->download([
    'FileId' => $file->getId()

    // Can also identify the file via bucket and path:
    // 'BucketName' => 'my-special-bucket',
    // 'FileName' => 'path/to/file'

    // Can also save directly to a location on disk. This will cause download() to not return file content.
    // 'SaveAs' => '/path/to/save/location'
]);
```

## Installation

Installation is via Composer:

```bash
$ composer require cwhite92/b2-sdk-php
```

## Tests

Tests are run with PHPUnit. After installing PHPUnit via Composer:

```bash
$ vendor/bin/phpunit
```

## Contributors

Feel free to contribute in any way you can whether that be reporting issues, making suggestions or sending PRs. :)

## License

MIT.
