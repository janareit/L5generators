Laravel 5 Generators
==============
(suitable for 5.2)

[![Latest Version](https://img.shields.io/github/release/janareit/laravel5generators.svg?style=flat-square)](https://github.com/janareit/laravel5generators/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/janareit/laravel5generators.svg?style=flat-square)](https://packagist.org/packages/janareit/laravel5generators)

This is a custom fork from http://sky.pingpong-labs.com/docs/2.0/generators
Official documentation available from there.

Its forked for better match my own needs for quick scaffolding in different Laravel 5 projects.

### Additions/Changes from original:

1. RouteModel binding functionality added and scaffolded to existiong file
2. Controller actions upgraded to inject Model classes to take use of RouteModelBindings
3. 'extends' functionality for views now possible to input from scaffold command
4. 'prefix' can be multi-level deep. For example `--prefix=Main/Admin`
5. Models stored to `App\Repositories` folder
5. 'responsive' generates responsive index view instead of table style


### Example usage

## Preparations:

1. To correctly edit with scaffolding `RouteServiceProvider.php` file you need to add a comment line inside your `boot` method:
```
//scaffolded routes will appear here [do not remove]
```

2. Add provider to app.php config
```
'janareit\laravel5generators\GeneratorsServiceProvider::class'
```


## Run from console for example:
```
php artisan generate:scaffold moulding_machine --fields="name:string, number:tinyInteger:unsigned, active:boolean" --prefix=Masterdata/Manufacturing --responsive --force --extends="layouts.master" --no-question
```

**PS: layouts.master will be stored to views root directory for security reasons so you don't overwrite your original master by accident.
If you don't have a layout master you should move it to `resources/views/layouts`**


This should output (no questions asked, as last flag declares):
```
Model created successfully.
Migration created successfully.
Seed created successfully.
Form request created successfully.
Form request created successfully.
Controller created successfully.
Migrated: 2015_06_05_113543_create_moulding_machines_table
View created successfully.
View created successfully.
View created successfully.
View created successfully.
View created successfully.
View created successfully.
Route appended successfully.
RouteServiceProvider appended successfully.
```


Generated files are:
```
app/Http/Controllers/Masterdata/Manufacturing/MouldingMachinesController.php
app/Http/Requests/Masterdata/Manufacturing/Machines/CreateMouldingMachineRequest.php
app/Http/Requests/Masterdata/Manufacturing/Machines/UpdateMouldingMachineRequest.php
app/Repositories/Masterdata/Manufacturing/MouldingMachine.php
database/migrations/2015_06_05_113543_create_moulding_machines_table.php
database/seeds/MouldingMachinesTableSeeder.php
resources/views/masterdata/manufacturing/moulding_machines/create.blade.php
resources/views/masterdata/manufacturing/moulding_machines/edit.blade.php
resources/views/masterdata/manufacturing/moulding_machines/form.blade.php
resources/views/masterdata/manufacturing/moulding_machines/index.blade.php
resources/views/masterdata/manufacturing/moulding_machines/show.blade.php
resources/views/layouts.master.blade.php //this is in case you generate new layout. For security it's not overwriting directly to layouts path.
```

Additions to existing files are:
```
routes.php

Route::resource('masterdata/manufacturing/moulding_machines', 'Masterdata\Manufacturing\MouldingMachinesController');
```

```
RouteServiceProvider.php

$router->model('moulding_machines', 'App/Repositories/Masterdata/Manufacturing/MouldingMachine');
```

If all worked out correctly you should be able to see your newly created CRUD pages at yourdomain/masterdata/manufacturing/moulding_machines url.


### Credits
Thank you guys at Pingpong labs (https://github.com/pingpong-labs/generators)!