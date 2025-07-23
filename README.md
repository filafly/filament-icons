<p class="filament-hidden" align="center">
    <img src="images/filament-icons.png" alt="Banner" style="width: 100%; max-width: 800px;" />
</p>
A package to replace Filament's default Heroicons with your preferred icon set, providing unified icon management with style variations and overrides.

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
- Filament v4
- [Blade Icons](https://github.com/blade-ui-kit/blade-icons) implementation of desired icon set
If there isn't a Blade Icon implementation, you'll need to add it yourself. If you want to use Filament Icons in a Filament v3 project, use branch 1.x.

## Implementation
1. Define all icons in the set with styles baked into the enum cases and implement the `ScalableIcon` interface.

```php
use Filament\Support\Contracts\ScalableIcon;
use Filament\Support\Enums\IconSize;

enum MyIconSet: string implements ScalableIcon
{
    case ArrowUp = 'arrow-up';
    case ArrowUpSolid = 'arrow-up-solid';
    case ArrowDown = 'arrow-down';
    case ArrowDownSolid = 'arrow-down-solid';
    case Search = 'search';
    case SearchSolid = 'search-solid';
    ...

    public function getIconForSize(IconSize $size): string
    {
        // Your implementation of the ScalableIcon interface
        // This can be as simple as `return "my-icon-set-{$this->value}"`
    }
}
```

2. Create an implementation of `Filafly\Icons\IconSet` with a class name that follows the pattern `MyIconSetIcons` (replace `MyIconSet` with the name of your icon set), and set `$pluginId` and `$iconEnum`.

```php
use Filafly\Icons\IconSet;

class MyIconSetIcons extends IconSet
{
    protected string $pluginId = 'filafly-filament-my-icons';

    protected mixed $iconEnum = MyIconSet::class;
}
```

> **Icon Prefix**: The system automatically guesses the icon prefix by taking the lowercase class name of your `$iconEnum` (e.g., `MyIconSet` becomes `myiconset`). This prefix is prepended to enum values when registering with Blade Icons (e.g., `myiconset-search-solid`).
> 
> If your Blade Icons package uses a different prefix than the guessed name, you can override it by setting `$iconPrefix`:
> ```php
> protected string $iconPrefix = 'my-custom-prefix';
> ```

3. Map all Filament icon aliases to your specific icon enum cases (with styles baked in).

```php
use Filament\View\PanelsIconAlias;

protected array $iconMap = [
    PanelsIconAlias::GLOBAL_SEARCH_FIELD => MyIconSet::SearchSolid,
    PanelsIconAlias::PAGES_DASHBOARD_ACTIONS_FILTER => MyIconSet::FunnelSolid,
    PanelsIconAlias::PAGES_DASHBOARD_NAVIGATION_ITEM => MyIconSet::HouseDuotone,
    ...
];
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
