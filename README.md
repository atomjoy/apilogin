# Laravel api login

Install with composer and update.

## Databases

```sh
# Create tables
php artisan migrate

# Refresh tables
php artisan migrate:fresh
```

### Activation page

```php
<?php

use Atomjoy\Apilogin\Http\Controllers\ActivateController;

# Email activation link route
Route::get('/activate/{id}/{code}', [ActivateController::class, 'index'])->name('activation');
```

### Run server

```sh
php artisan serve --host=localhost --port=8000
```

## Routes prefix: /web/api

Routes in file routes/web.php

## Validation request

Route params search in: src/Http/Requests

## Overwrite email, translations, config

```sh
php artisan lang:publish
php artisan vendor:publish --tag=apilogin-views --force
php artisan vendor:publish --tag=apilogin-lang --force
php artisan vendor:publish --tag=apilogin-config--force
```

## Tests

Copy testsuite Apilogin from phpunit.xml

```sh
<testsuite name="Apilogin">
  <directory suffix="Test.php">./vendor/atomjoy/apilogin/tests/Dev</directory>
</testsuite>
```

### Comment fallback route

Comment the fallback route while testing!

```php
<?php
// Vue catch all
// Route::fallback(function () {
// 	return view('vue');
// });
?>

### Run tests

```sh
php artisan test --stop-on-failure --testsuite=Apilogin
```

### Local package Laravel composer.json

```json
{
  "repositories": [
  {
   "type": "path",
   "url": "packages/atomjoy/apilogin"
  }
 ],
 "require": {
  "atomjoy/apilogin": "dev-main",
 }
}
```
