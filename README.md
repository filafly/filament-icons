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

## Community Implementations
- (none yet...)

# Creating Your Own Icon Set
This package allows you to create your own icon set implementations, enabling you to integrate any icon library that has a Blade Icons implementation with Filament. Here's how to get started:

## Requirements
- Filament v4
- [Blade Icons](https://github.com/blade-ui-kit/blade-icons) implementation of desired icon set
If there isn't a Blade Icon implementation, you'll need to add it yourself. If you want to use Filament Icons in a Filament v3 project, use branch 1.x.

## Implementation
1. Add the available icon styles and the corresponding style string used by Blade Icons.

```php
enum MyIconSetStyle: string
{
    case Regular = '';
    case Solid = '-solid';
}
```
> **Note:** The package automatically generates style-specific methods based on your `$styleEnum`. For example, if you have `Solid` and `Duotone` cases in your style enum, you can use `->solid()` and `->duotone()` to set the current style.

2. Define all icons in the set and implement the `ScalableIcon` interface.

```php
use Filament\Support\Contracts\ScalableIcon;
use Filament\Support\Enums\IconSize;

enum MyIconSet: string implements ScalableIcon
{
    case ArrowUp = 'arrow-up';
    case ArrowDown = 'arrow-down';
    ...

    public function getIconForSize(IconSize $size): string
    {
        // Your implementation of the ScalableIcon interface
    }
}
```

3. Create an implementation of `Filafly\Icons\IconSet` with a class name that follows the pattern `MyIconSetIcons` (replace `MyIconSet` with the name of your icon set), and set `$pluginId`, `$iconEnum`, `$styleEnum`, and `$defaultStyle`.

```php
use Filafly\Icons\IconSet;

class MyIconSetIcons extends IconSet
{
    protected string $pluginId = 'filafly-filament-my-icons';

    protected mixed $iconEnum = MyIconSet::class;

    protected mixed $styleEnum = MyIconSetStyle::class;

    protected mixed $defaultStyle = MyIconSetStyle::Regular;
}
```

4. Map all Filament icon aliases to your desired icons. Do not include any style specific string fragments such as "regular", "duotone", "-o", or "far-".

```php
protected array $iconMap = [
    'panels::global-search.field' => MyIconSet::Search,
    'panels::pages.dashboard.actions.filter' => MyIconSet::Funnel,
    'panels::pages.dashboard.navigation-item' => MyIconSet::House,
    ...
];
```

5. If the Blade Icon implementation of the icon set prefixes the style string to the icon name, make sure to indicate this so icon names are built properly.

```php
class MyIconSetIcons extends IconSet
{
    protected bool $shouldPrefixStyle = true;
}
```

## Optional Configuration

### Setting a default style
You can specify which style will be the default. If not specified, the first style will be used.

```php
protected string $defaultStyle = MyIconSetStyle::Regular;
```

### Forcing styles
You can force specific icons to always use a particular style using the `forcedStyles` array. This overrides any style settings, including the default style and dynamic style changes. This is particularly useful when certain icons are only available in specific styles, such as brand icons that may only exist in a single style variant.

```php
protected array $forcedStyles = [
    'carat-up' => MyIconSetStyle::Solid,
];
```

### Advanced configuration
For advanced customization, you can create additional methods that can be fluently chained on your `IconSet` class. These methods can modify the internal arrays and properties to control the behavior of the icon set.

For example, you could add a `free()` method to restrict styles to only those available in the free version of an icon set:

```php
// In your icon set
public function free(): self
{
    foreach ($onlyRegularExists as $icon) {
        $this->forcedStyles[$icon] = MyIconSet::Regular;
    }

    return $this;
}

// In the Filament panel
...
->plugin(
    MyIconSetIcons::make()->free()
)
...
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
