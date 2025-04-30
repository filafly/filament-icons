# Filament Icons

A small Laravel package that provides a central service provider for managing multiple Filament icon-set drivers.

## Installation

Install the core package and publish its config:

```bash
composer require filafly/filament-icon
```

## Configuration

In your `config/filament-icons.php`, choose which driver to use and map driver keys to their fully qualified ServiceProvider classes:

```php
return [
    // The active icon driver (e.g. 'heroicons', 'phosphor')
    'driver'  => env('FILAMENT_ICON_DRIVER', 'heroicons'),

    // Register available drivers and their providers
    'drivers' => [
        'heroicons' => \Filament\IconPlugin\HeroiconsServiceProvider::class,
        'phosphor'  => \Filafly\PhosphorIconReplacement\PhosphorIconServiceProvider::class,
        // Add your own packs here...
    ],
];
```

Set your driver in your `.env`:

```dotenv
FILAMENT_ICON_DRIVER=phosphor
```

Then rebuild your config cache:

```bash
php artisan config:clear
php artisan config:cache
```

Icons will automatically swap out based on the selected driver.

## Driver Author Guide

To build a new icon-set pack:

1. Implement the `\Filafly\FilamentIcons\IconDriverContract` interface in your ServiceProvider.
2. Declare that ServiceProvider in your `composer.json` under `extra.laravel.providers` so Laravel auto-discovers it.
3. Publish your package and add its key to the `drivers` array in `config/filament-icons.php`.

Happy iconing! 🎨