# viewpages

Support view/rendering of Laravel pages and templates from a database.

Can be used for content management, admin interfaces (e.g. using AdminLTE or other
front end frameworks), etc.

**UNDER CONSTRUCTION, NOT ENTIRELY STABLE YET**

## Rationale

The lack of ability to have database backed views, templates, and layouts is one of the
missing features that prevents Laravel from being used to create a truly dynamic CMS.  This
package aims to fix that.

TerrePorter partially solves this issue with his StringBladeCompiler package, which is
used by this package as a dependency.  His package was originally based on Flynsarmy/laravel-db-blade-compiler
which did support taking a blade from a model object but is no longer maintained.

# Installation

Add the package using composer from the command line:

```
    composer require delatbabel/viewpages
```

Alternatively, pull the package in manually by adding these lines to your composer.json file:

```
    "require": {
        "delatbabel/viewpages": "~1.0"
    },
```

Once that is done, run the composer update command:

```
    composer update
```

## Register Service Provider

After composer update completes, remove this line from your config/app.php file in the 'providers'
array (or comment it out):

```
    Illuminate\View\ViewServiceProvider::class
```

Replace it with this line:

```
    Delatbabel\ViewPages\ViewPagesServiceProvider::class,
```

## Incorporate and Run the Migrations

Finally, incorporate and run the migration scripts to create the database tables as follows:

```php
    php artisan vendor:publish --tag=migrations --force
    php artisan vendor:publish --tag=seeds --force
    php artisan migrate
```

Prior to running the migration scripts you may want to alter the scripts themselves, or
alter the base templates contained in database/seeds/examples.  The ones provided are a
few examples based on [AdminLTE](https://almsaeedstudio.com/).

# How to Use This Package

## Creating Views

* Install and run the migrations as per the above.
* Populate the vpages table with your templates.  They do not have to look any different
  to standard Laravel blade templates -- see the section below on **Blade Compilation**.
* In addition to the *content* column which should contain the template or page content,
  populate the following columns:
* **pagekey** -- page lookup key.
* **url** -- page lookup URL, may be useful when you want to look up page content by URL.
* **name** -- a descriptive name of the page, eg "main website home page".
* **description** -- a longer description of the page.

The important thing here is the **pagekey**.  This basically takes the place of the view
name used to find the view in the existing Laravel View facade.  So, for example, if you would
normally use View::make("dashboard.sysadmin"); to find the view, you would normally store
the view on disk in resources/views/dashboard/sysadmin.blade.php.  Instead you would store the
view in the vpages table with pagekey = "dashboard.sysadmin".

## Creating Templates

You can still use templates (layouts) as you normally would in Laravel.  For example, your
template can contain this:

```html
<html>
<head><title>{{ $page_title }}</title></head>
<body>
@yield('body)
</body>
</html>
```

The body can then contain this:

```html
@extends('layouts.main')

@section('body)
<p>Body text goes here</p>
@endsection
```

Store the template in the vpages table with pagekey = 'layouts.main' and it will automatically
be found and extended by your body view.

See [Template Inheritance](https://laravel.com/docs/5.1/blade#template-inheritance) for more details.

## Using Templates

Once the templates are created, you can use them just like any other view file, e.g.

```php
    return View::make("dashboard.sysadmin")
        ->with('page_title', 'System Administrator Dashboard')
        ->with('tasks', $tasks);
```

The underlying Factory class will try to find the view by doing the following steps in
order until a hit is found:

* Look in the vpages table for a vpage with pagekey = dashboard.sysadmin.
* Look in the vpages table for a vpage with url = dashboard.sysadmin.
* Look on disk for a view called resources/views/sysadmin/dashboard.blade.php
* Look in the vpages table for a vpage with pagekey = errors.410
* Look in the vpages table for a vpage with pagekey = errors.404

# TODO

* Extract the logic to find a page for a specific website from the make function and put
  it into a customised BelongsToMany class.
* More testing.
* More documentation.
* Maybe a set of admin controllers to update / edit content in the database.

## Callouts

The original package that this was derived from had the idea of callouts.  This meant that
views could include calls like this:

```php
    {{ __o('toolbox@functionname') }}
```

__o was a helper function that called the Controller::call() function in Laravel 3 to render
the output of the "functionname" action on the toolbox controller in a HMVC like manner.  HMVC
is really no longer supported by Laravel 5 (and Taylor thinks that HMVC is a bad idea, which
I have to disagree with) so we need some other way of pulling in dynamic content.  This will
probably be via Repository or Service classes somehow.

[Service Injection](https://laravel.com/docs/5.1/blade#service-injection) may already work, I
haven't tested it.

## Website Data Objects and Blocks

These are website dependent data blocks.  It may be preferable to store website dependent
data including renderable data blocks in the configs table, which is of course what it was
designed for.

# Architecture

I worked with a CMS system based on Laravel 3 that was fairly poor in its implementation,
this package is designed to be a best practice implementation of what the Laravel 3 CMS
was supposed to be.

## Handling Directives

I have extended the Factory class within String_Blade_Compiler class to be able to have
@include refer to a page or template key instead of a file name.  See **Blade Compilation**
below.

## Handling View Names

A view can be found by name or URL.  A CMS may prefer to fetch views by URL, a system that
is just working on view names may prefer to fetch by page key (e.g. layouts.master).  The
Factory class attempts to find by key first, and then by URL.

If a view is not found in the database then a view by that key is searched for on disk.

If no view is found on the disk then the errors.410 and then the errors.404 views are searched
for in the database.

If no view is found at that point then an exception is thrown.

## Blade Compilation

Compilation of blade templates is a bit of a black art, that's poorly explained in the Laravel
documentation.  Basically, blade templates are all compiled to on-disk PHP files which are
then stored in storage/framework/views.  Once a compiled version of a template goes out of date
it is replaced with a newer copy.  The caching of these compiled templates normally depends on
the file date of the blade template file, however in this extension we make it depend on the
updated_at date of the template data in the database.

When a blade is compiled to PHP, the directives are compiled as follows:

### @extends / @section

These go together.  @extends compiles to:

```php
    echo $__env->make('layout.name', array_except(get_defined_vars(), array('__data', '__path')))->render();
```

@section and @endsecton compile to:

```php
    $__env->startSection('section_name');
    $__env->stopSection();
```

Note that the directives appear in the compiled file in the opposite order to which they appear
in the blade template file -- normally in the template file @extends would be at the top and
@section / @endsection would be below, in the compiled template file the compiled version of
@extends is at the end of the file.

### @yield

@yield('section_name') appears like this in the compiled file:

```php
    echo $__env->yieldContent('body');
```

### Use of $__env

The global variable $__env is actually an instance of Illuminate\View\Factory, or in the
String_Blade_Compiler extension it is an instance of Wpb\String_Blade_Compiler\Factory.

That class implements the necessary make(), startSection(), stopSection() and yieldContent()
functions, which make the content appear in the correct place.

The critical function is make() which has been extended so that it is able to pull the
view from the database instead of from disk.  The logic of pulling the view from the
database is all in the Vpage::make() function.  Once the content of the blade is pulled
from the database then it's passed back up to String_Blade_Compiler\Factory::make to do
the actual rendering.

## Service Provider

The service provider here is fairly simple -- however there are 2:

* ViewPagesServiceProvider -- does the normal registration of migrations, seeds, and also calls
  in ViewServiceProvider.
* ViewServiceProvider -- extends the Service Provider in String_Blade_Compiler so that my own
  Factory class is inserted when the factory is registered rather than the original.

## Model Class

The model class (Vpage) replaces the on-disk storage of template files, so that the Factory
class discussed above can pull templates from the database rather than disk.

# References

* https://github.com/Flynsarmy/laravel-db-blade-compiler
* https://github.com/TerrePorter/StringBladeCompiler (the 3.0 branch)
