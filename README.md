<p class="filament-hidden" align="center">
    <img src="images/filament-icons.png" alt="Banner" style="width: 100%; max-width: 800px;" />
</p>
A package to replace Filament's default Heroicons with your preferred icon set, providing unified icon management with style variations and overrides.

## Available Icon Sets

The following icon sets are available as separate packages that work with this core package:

### Official Implementations
- [Filament Phosphor Icons](https://github.com/filafly/filament-phosphor-icons)

### Community Implementations
- (none yet...)

## Creating Your Own Icon Set
This package allows you to create your own icon set implementations, enabling you to integrate any icon library that has a Blade Icons implementation with Filament. Here's how to get started:

### Requirements
The icon set must have a [Blade Icons](https://github.com/blade-ui-kit/blade-icons) implementation. If there isn't one, you'll need to add it yourself.

1. Create an implementation of `Filafly\FilamentIcons\IconSet` with a name that matches the [IconSet]Icons pattern and set the plugin ID.

```php
use Filafly\FilamentIcons\IconSet;

class PhosphorIcons extends IconSet
{
    protected string $pluginId = 'phosphor-for-filament';
```

2. Map all Filament icon aliases to your desired icons without the icon style specified.

```php
 protected function getIconMap(): array
    {
        return [
            'panels::global-search.field' => 'phosphor-magnifying-glass',
            'panels::pages.dashboard.actions.filter' => 'phosphor-funnel',
            'panels::pages.dashboard.navigation-item' => 'phosphor-house',
            ...
```

3. Add the available styles and the corresponding suffix.

```php
    public function getAvailableStyles(): array
    {
        return [
            'thin' => '-thin',
            'light' => '-light',
            'regular' => '',
            ...
```

4. Create individual style methods.

```php
    public function thin(): static
    {
        self::setStyle('thin');

        return $this;
    }
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.