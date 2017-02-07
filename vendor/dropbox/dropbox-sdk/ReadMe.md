# Dropbox SDK for PHP 5.3+

A PHP library to access [Dropbox's HTTP-based API](http://dropbox.com/developers/core/docs).

License: [MIT](License.txt)

Requirements:
  * PHP 5.3+, [with 64-bit integers](http://stackoverflow.com/questions/864058/how-to-have-64-bit-integer-on-php).
  * PHP [cURL extension](http://php.net/manual/en/curl.installation.php) with SSL enabled (it's usually built-in).
  * Must not be using [`mbstring.func_overload`](http://www.php.net/manual/en/mbstring.overload.php) to overload PHP's standard string functions.

[SDK API docs.](http://dropbox.github.io/dropbox-sdk-php/api-docs/v1.1.x/)

## Setup

If you're using [Composer](http://getcomposer.org/) for your project's dependencies, add the following to your "composer.json":

```
"require": {
  "dropbox/dropbox-sdk": "1.1.*"
}
```

If you're not using Composer, download the code, copy the "lib/" folder into your project somewhere, and include the "lib/Dropbox/autoload.php" in your code.  For example, if you copied the "lib/" and named it "dropbox-sdk/", you would do:

```php
// Do this only if you're not using a global autoloader (such as Composer's).
require_once "dropbox-sdk/Dropbox/autoload.php";
```

IMPORTANT: Many PHP installations have an insecure SSL implementation.  To check if your PHP installation is insecure, run the included "examples/test-ssl.php" script, either on the command line or via your web server.

## Get a Dropbox API key

You need a Dropbox API key to make API requests.
  * Go to: [https://dropbox.com/developers/apps](https://dropbox.com/developers/apps)
  * If you've already registered an app, click on the "Options" link to see the app's API key and secret.
  * Otherwise, click "Create an app" to register an app.  Choose "Dropbox API app", then "Files and datastores", then "Yes" or "No" [depending on your needs](https://www.dropbox.com/developers/reference#permissions).

Save the API key to a JSON file called, say, "test.app":

```
{
  "key": "Your Dropbox API app key",
  "secret": "Your Dropbox API app secret"
}
```

## Using the Dropbox API

Before your app can access a Dropbox user's files, the user must authorize your application using OAuth 2.  Successfully completing this authorization flow gives you an _access token_ for the user's Dropbox account, which grants you the ability to make Dropbox API calls to access their files.

  * Authorization example for a simple web app: [Web File Browser example](examples/web-file-browser.php)
  * Authorization example for a command-line tool: [Command-Line Authorization example](examples/authorize.php)

Once you have an access token, create a [`Client`](http://dropbox.github.io/dropbox-sdk-php/api-docs/v1.1.x/class-Dropbox.Client.html) and start making API calls.

You only need to perform the authorization process once per user.  Once you have an access token for a user, save it somewhere persistent, like in a database.  The next time that user visits your app, you can skip the authorization process and go straight to making API calls.

## Running the Examples and Tests

1. Download this repository.
2. Save your Dropbox API key in a file called "test.app".  See: [Get a Dropbox API key](#get-a-dropbox-api-key), above.

### authorize.php

This example runs through the OAuth 2 authorization flow.

```
./examples/authorize.php test.app test.auth
```

This produces a file named "test.auth" that has the access token.  This file can passed in to the other examples.

### account-info.php

A trivial example that calls the /account/info API endpoint.

```
./examples/account-info.php test.auth
```

(You must first generate "test.auth" using the "authorize" example above.)

### web-file-browser.php

A tiny web app that runs through the OAuth 2 authorization flow and then uses Dropbox API calls to let the user browse their Dropbox files.

**Required**: copy one of the ".app" files you created previously to "examples/web-file-browser.app".

**Using PHP built-in web server (PHP 5.4+).**

1. Go to the Dropbox API [app console](https://www.dropbox.com/developers/apps), go to the API app you configured in "web-file-browser.app", go to the _OAuth redirect URIs_ section, and add "http://localhost:5000/dropbox-auth-finish".
2. Run "`php web-file-browser.php`".
3. Point your browser at "http://localhost:5000/".

**Using an existing web server setup.**

1. Copy the entire SDK folder to your web server's document path.  For example, let's say the script is accessible at "http://localhost/~scooby/dropbox/examples/web-file-browser.php".
2. Go to the Dropbox API [app console](https://www.dropbox.com/developers/apps), go to the API app you configured in "web-file-browser.app", go to the _OAuth redirect URIs_ section, and add "http://localhost/~scooby/dropbox/examples/web-file-browser.php/dropbox-auth-finish".
3. Point your browser at "http://localhost/~scooby/dropbox/examples/web-file-browser.php".

### Running the Tests

1. run: `composer install --dev` to download the dependencies.  (You'll need [Composer](http://getcomposer.org/download/).)
2. Put an "auth info" file in "test/test.auth".  (You can generate "test/test.auth" using the "authorize.php" example script.)

```
./vendor/bin/phpunit test/
```
