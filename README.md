# Blade

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/blade.svg)](https://packagist.org/packages/jenssegers/blade) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/blade.svg)](https://coveralls.io/r/jenssegers/blade)

A maintained fork of jenssegers/blade, the standalone version of [Laravel's Blade templating engine](https://laravel.com/docs/5.8/blade) for use outside of Laravel.

<p align="center">
<img src="https://jenssegers.com/static/media/blade2.png" height="200">
</p>

## Installation

Install using composer:

```bash
composer require rcalicdan/blade
```

## Usage

Create a Blade instance by passing it the folder(s) where your view files are located, and a cache folder. Render a template by calling the `make` method. More information about the Blade templating engine can be found on http://laravel.com/docs/5.8/blade.

```php
use Rcalicdan\Blade\Blade;
use Rcalicdan\Blade\Container as BladeContainer;

$container = new BladeContainer();
$blade = new Blade('views', 'cache', $container);

echo $blade->make('homepage', ['name' => 'John Doe'])->render();
```

Alternatively you can use the shorthand method `render`:

```php
echo $blade->render('homepage', ['name' => 'John Doe']);
```

You can also extend Blade using the `directive()` function:

```php
$blade->directive('datetime', function ($expression) {
    return "<?php echo with({$expression})->format('F d, Y g:i a'); ?>";
});
```

Which allows you to use the following in your blade template:

```
Current date: @datetime($date)
```

The Blade instances passes all methods to the internal view factory. So methods such as `exists`, `file`, `share`, `composer` and `creator` are available as well. Check out the [original documentation](https://laravel.com/docs/5.8/views) for more information.

## Integrations

- [Phalcon Slayer Framework](https://github.com/phalconslayer/slayer) comes out of the box with Blade.

## Credits

This package is a fork of [jenssegers/blade](https://github.com/jenssegers/blade) which appears to be no longer maintained. This fork includes compatibility fixes for modern PHP environments while maintaining the same functionality.