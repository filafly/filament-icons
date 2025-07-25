<p class="filament-hidden" align="center">
    <img src="images/filament-icons.png" alt="Banner" style="width: 100%; max-width: 800px;" />
</p>
A package to replace Filament's default Heroicons with your preferred icon set, providing unified icon management with style variations and overrides.

# Features
- **Unified Icon Management**: Standardize all icons across your Filament project.
- **Style Variations**: Switch between icon styles (e.g., `solid`, `regular`, `light`) with a single method call.
- **Granular Overrides**: Override specific icons or aliases at the global or component level.
- **Extensible**: Easily create your own icon set implementations for any icon library with a Blade Icons package.
- **Developer-Focused**: Designed for developers creating icon set implementations, not just end-users.

# Available Icon Sets

The following icon sets are available as separate packages that work with this core package:

## Official Implementations
- [Phosphor](https://github.com/filafly/filament-phosphor-icons)
- [Font Awesome](https://github.com/filafly/filament-font-awesome-icons)
- [Iconoir](https://github.com/filafly/filament-iconoir-icons)
- [Carbon](https://github.com/filafly/filament-carbon-icons)

## Community Implementations
- (none yet...)

# Creating Your Own Icon Set
This package allows you to create your own icon set implementations, enabling you to integrate any icon library that has a Blade Icons implementation with Filament. Here's how to get started:

## Requirements
- An icon set with a [Blade Icons](https://github.com/blade-ui-kit/blade-icons) implementation. If one doesn't exist, you'll need to create it.
- Your project must use Filament v3. For Filament v2, use the `1.x` branch of this package.

## Implementation Steps

### 1. Define the Icon Enum
Create a PHP enum that defines all icons in your set. The enum cases should have styles baked into their names (e.g., `SearchRegular`, `SearchSolid`).

```php
<?php

namespace App\Enums;

enum MyIcon: string
{
    case SearchRegular = 'search';
    case SearchSolid = 'search-solid';
    // ... other icons
}
```

### 2. (Optional) Define the Style Enum
If your icon set supports multiple styles (e.g., regular, solid, duotone), create a `StyleEnum` that implements `Filafly\Icons\Contracts\StyleEnum`. This ensures your enum provides the necessary methods for the style system.

```php
<?php

namespace App\Enums;

use Filafly\Icons\Contracts\StyleEnum as StyleEnumContract;

enum MyIconStyle: string implements StyleEnumContract
{
    case Regular = 'regular';
    case Solid = 'solid';

    public function getStyleName(): string
    {
        return $this->value;
    }

    public function getEnumSuffix(): string
    {
        return ucfirst($this->value);
    }

    public static function getStyleNames(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromStyleName(string $styleName): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->getStyleName() === $styleName) {
                return $case;
            }
        }
        return null;
    }
}
```

### 3. Create the IconSet Class
Create a class that extends `Filafly\Icons\IconSet`. This class will manage your icon set's integration with Filament.

```php
<?php

namespace App\Icons;

use App\Enums\MyIcon;
use App\Enums\MyIconStyle;
use Filafly\Icons\IconSet;

class MyIcon extends IconSet
{
    protected string $pluginId = 'vendor-filament-my-icons';
    protected string $iconPrefix = 'my-icon'; // Optional: if different from guessed prefix
    protected mixed $iconEnum = MyIcon::class;
    protected ?string $styleEnum = MyIconStyle::class; // Optional
}
```

> **Icon Prefix**: The system automatically guesses the icon prefix from your `$iconEnum`'s class name (e.g., `MyIcon` becomes `myicon`). If your Blade Icons package uses a different prefix, set it with `$iconPrefix`.

### 4. Map Filament Icon Aliases
In your `IconSet` class, map Filament's icon aliases to your icon enum cases in the `$iconMap` array.

```php
protected array $iconMap = [
    'panels::global-search.field' => MyIcon::SearchRegular,
    'panels::pages.dashboard.actions.filter' => MyIcon::SearchSolid,
    // ... other mappings
];
```

# Advanced Usage
The `IconSet` class provides powerful methods for style transformations and granular overrides.

## Global Styling
You can set a global style for all icons in your set. This is useful for applying a consistent look across your application.

### Using the `style()` method:
```php
MyIcon::make()->style('solid');
```

### Using dynamic style methods:
If you have a `StyleEnum` defined, you can use dynamic methods named after your styles:

```php
MyIcon::make()->solid();
MyIcon::make()->regular();
```

## Granular Overrides
Overrides allow you to change icons for specific aliases or replace one icon with another. The override precedence is:

1. **Exact Overrides**: `overrideAlias()` and `overrideIcon()`
2. **Style Overrides**: `overrideStyleForAlias()` and `overrideStyleForIcon()`
3. **Global Style**: `style()` or dynamic methods
4. **Default**: The original mapping in `$iconMap`

### `overrideAlias(string $alias, mixed $iconCase)`
Overrides a specific Filament alias to use a different icon.

```php
MyIcon::make()->overrideAlias('panels::global-search.field', MyIcon::SearchSolid);
```

### `overrideAliases(array $overrides)`
Overrides multiple aliases at once.

```php
MyIcon::make()->overrideAliases([
    'panels::global-search.field' => MyIcon::SearchSolid,
    'panels::pages.dashboard.actions.filter' => MyIcon::SearchRegular,
]);
```

### `overrideIcon(mixed $fromIconCase, mixed $toIconCase)`
Replaces one icon enum case with another across all its uses.

```php
MyIcon::make()->overrideIcon(MyIcon::SearchRegular, MyIcon::SearchSolid);
```

### `overrideIcons(array $overrides)`
Replaces multiple icons at once.

```php
MyIcon::make()->overrideIcons([
    MyIcon::SearchRegular => MyIcon::SearchSolid,
]);
```

### `overrideStyleForAlias(string|array $aliases, string|object $style)`
Applies a specific style to one or more aliases.

```php
MyIcon::make()->overrideStyleForAlias('panels::global-search.field', 'solid');
// or
MyIcon::make()->overrideStyleForAlias(['panels::global-search.field'], MyIconStyle::Solid);
```

### `overrideStyleForIcon(mixed $iconCases, string|object $style)`
Applies a specific style to one or more icon enum cases.

```php
MyIcon::make()->overrideStyleForIcon(MyIcon::SearchRegular, 'solid');
// or
MyIcon::make()->overrideStyleForIcon([MyIcon::SearchRegular], MyIconStyle::Solid);
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
