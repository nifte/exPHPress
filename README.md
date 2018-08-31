# exPHPress
A lightweight express-like router for PHP with minimal setup required

[![GitHub](https://img.shields.io/github/license/nifte/exPHPress.svg)](https://github.com/nifte/exPHPress/blob/master/LICENSE)
[![PHP](https://img.shields.io/badge/PHP-%5E5.6-blue.svg)](https://php.net/downloads.php)

## Features
- Lightweight - only 1 file to include
- No dependencies - 100% pure PHP
- Works on both Apache and NGINX

## Example
```php
<?php

require_once 'exPHPress.php';
$app = new exPHPress;

$app->get('/', function($req, $res) {
	$res->sendFile('home.php');
});

$app->get('/profile/:id', function($req, $res) {
	$res->sendFile('profile.php', ['user_id' => $req['id']]);
});

$app->get('/api/users/:id', function($req, $res) {
	$res->setHeader('Content-Type', 'application/json');
	$res->json(['user_id' => $req['id']]);
});
```

## Getting Started
#### Preparing the server (Apache)
Add the following to your `httpd.conf` file:
```xml
<Directory "/var/www/html">
	AllowOverride All
	Require all granted
</Directory>
```
Change the directory name to the directory where your app will live

Then add the following `.htaccess` file to your app directory:
```
RewriteEngine On
RewriteCond %{REQUEST_URI} !\.(js|css|png|jpg|jpeg|gif|svg)$
RewriteRule ^ app.php [L,QSA]
```
**Note:** Be sure to restart the server afterwards

#### Setting up your app
1. Create an `app.php` file in your app directory
2. Download `exPHPress.php` and put it in the same directory
3. Add the following to `app.php`:
```php
<?php
require_once 'exPHPress.php';
$app = new exPHPress;
```
And that's it!

## How to Use
#### Simple routing
```php
$app->get('/', function() {
	echo 'Hello world!';
});
```
- `$app->get()` defines a route using the http GET request method
	- You can also use `put()`, `post()`, or `delete()`
- `'/'` is the URL/pattern to be tested against the requested URI
- `function()` is the function that is called if the requested URI matches `'/'`

#### Redirecting a route
```php
$app->get('/profile', function($req, $res) {
	$res->redirect('/profile/nifte');
});
```
- `$res->redirect()` is the response function for redirecting one route to another
	- The redirected route will keep the same http request method

#### Accessing request parameters
```php
$app->get('/greeting/:name', function($req) {
	$name = $req['name'];
	echo "Hello $name!";
});
```
- `:name` is a parameter we want to retrieve from the requested URI
	- Request parameters are defined by prepending them with a colon (`:`)
- `$req` is an associative array containing the request parameters and their values
	- The value of a request parameter can be accessed by referencing it in `$req`, such as `$req['name']`

#### Sending a file
```php
$app->get('/profile', function($req, $res) {
	$res->sendFile('profile.php');
});
```
- `$res` is the response object, containing some useful built-in functions
- `$res->sendFile()` is the response function for sending a file to the client

#### Passing variables to a file
```php
$app->get('/profile/:id', function($req, $res) {
	$res->sendFile('profile.php', [
		'user_id' => $req['id']
	]);
});
```
- `$res->sendFile()` accepts an optional second parameter - an associative array of variables to be extracted to the file being sent
	- In `profile.php`, the variable `$user_id` would be equal to the request parameter `:id`

#### Setting a static directory
```php
$app->static('public/views');
```
- `$app->static()` defines a default directory for `$res->sendFile()` to send files from
- `public/views` is the new directory in which `$res->sendFile()` will look for files

#### Sending JSON data
```php
$app->get('/api/users/:id', function($req, $res) {
	$res->json([
		'user_id' => $req['id']
	]);
});
```
- `$res->json()` is the response function for sending json data to the client
	- Acceptable inputs are associative arrays and valid json strings

#### Sending an HTTP status
```php
$app->get('/', function($req, $res) {
	$res->sendStatus(201);
});
```
- `$res->sendStatus()` is the response function for sending an http status code
	- This route will respond with `http 201`

#### Setting HTTP headers
```php
$app->get('/', function($req, $res) {
	$res->setHeader('Content-Type', 'application/json');
});
```
- `$res->setHeader()` is the response function for setting http headers
	- Acceptable inputs are a single key and value, or an associative array of multiple keys and values

#### Handling invalid routes
```php
$app->invalid(function($req, $res) {
	$res->sendStatus(404);
	$res->sendFile('404.php');
});
```
- `$app->invalid()` defines the function to be run when the requested URI does not match any of the defined routes
	- If the `$app->invalid()` function is not defined, invalid routes will simply return `http 404` with the message 'Page not found.'