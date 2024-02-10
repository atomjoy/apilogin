# Laravel API Authentication

Multi guard authentication API with sessions in Laravel.

## COnfig

Install databases and update with composer.

```sh
# Update
composer update

# Create permissions migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Then change the beginning of the permission migration file name to
2023_01_01_100000_create_permission_tables.php
```

## Databases

```sh
# notifications, storage
php artisan notifications:table
php artisan storage:link

# Create tables
php artisan migrate

# Refresh tables
php artisan migrate:fresh
```

### Add in User model

Add profil, address, notifications relations (required).

```php
<?php

namespace App\Models;

use Atomjoy\Apilogin\Contracts\HasProfilAddress;
use Atomjoy\Apilogin\Contracts\HasRolesPermissions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasProfilAddress, HasRolesPermissions;
    use HasRoles;

    /**
    * Model table.
    */
    protected $table = 'users';

    /**
    * Auth guard.
    */
    protected $guard = 'web';

    /**
    * Append user relations (optional).
    */
    protected $with = ['profile', 'roles'];

    // ...
}
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
php artisan vendor:publish --tag=apilogin-config--force
php artisan vendor:publish --tag=apilogin-views --force
php artisan vendor:publish --tag=apilogin-lang --force
# Permissions seeder migrations
php artisan db:seed --class=ApiloginPermissionsSeeder
```

## Default admin credentials

Change before running the migration. See migration file 2023_08_04_105808_create_admin_users_table.php.

```php
// config/apilogin.php
return [
  // Admin users emails (email with dns mx)
  'super_admin_email' => 'superadmin@gmail.com',
  'admin_email' => 'admin@gmail.com',
  'worker_email' => 'worker@gmail.com',

  // Admin users passsword
  'super_admin_password' => 'Password123#',
  'admin_password' => 'Password123#',
  'worker_password' => 'Password123#',
]
```

### Overwrite s3 disk (avatar, images upload), admin emails

Disable amazon S3 disk overwriting if you have it installed (optional).

```php
// config/apilogin.php
return [
  // Disable Storage::disk s3 to public overwrite
  'overwrite_disk_s3' => false,
]
```

## Tests

Copy testsuite Apilogin from phpunit.xml

```sh
<testsuite name="Apilogin">
  <directory suffix="Test.php">./vendor/atomjoy/apilogin/tests/Dev</directory>
</testsuite>
```

### Mysql user and db

```sql
CREATE DATABASE IF NOT EXISTS laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON *.* TO root@localhost IDENTIFIED BY 'toor' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO root@127.0.0.1 IDENTIFIED BY 'toor' WITH GRANT OPTION;
```

### Comment fallback route

Chenge or comment the fallback route while testing!

```php
<?php
// Add login route
Route::get('/login', function () {
  return view('vue');
})->name('login');

// Disable in testing
if (!app()->runningUnitTests()) {
  // Vue catch all
  Route::fallback(function () {
    return view('vue');
  });
}
```

### 2FA auth

Two factor auth redirection url (vue).

```sh
/login/f2a/{hash}
/admin/login/f2a/{hash}
```

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

## Notifications config

Run first: php artisan notifications:table

```php
<?php

use App\Models\User;
use Atomjoy\Apilogin\Notifications\Contracts\NotifyMessage;
use Atomjoy\Apilogin\Notifications\DbNotify;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
  $msg = new NotifyMessage();
  $msg->setContent('Hello max your LINK_SIGNUP and LINK_SIGNIN link (Register LINK_SIGNUP).');
  $msg->setLink('LINK_SIGNUP', 'https://example.com/signup', 'Sign Up');
  $msg->setLink('LINK_SIGNIN', 'https://example.com/signin', 'Sign In');

  $user = User::first();
  $user->notify(new DbNotify($msg));
  $user->notifyNow(new DbNotify($msg));

  return $user->notifications()->offset(0)->limit(15)->get()->each(function ($n) {
    $n->formatted_created_at = $n->created_at->format('Y-m-d H:i:s');
  });
});
```

## LICENSE

This project is licensed under the terms of the GNU GPLv3 license.
