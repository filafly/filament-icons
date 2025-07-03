<?php

namespace Filafly\Icons;

use BackedEnum;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Enums\Icon;
use Filament\Support\Enums\IconStyle;
use Filament\Support\Facades\FilamentIcon;

abstract class IconSet implements Plugin
{
    /*
    |--------------------------------------------------------------------------
    | Set configuration
    |--------------------------------------------------------------------------
    */
    protected string $pluginId;

    protected ?IconStyle $defaultStyle = null;

    protected bool $shouldPrefixStyle = false;

    protected array $styleMap = [];

    protected array $iconMap = [];

    protected array $forcedStyles = [];

    /*
    |--------------------------------------------------------------------------
    | Icon Swap
    |--------------------------------------------------------------------------
    */
    protected ?IconStyle $currentStyle = null;

    protected array $overriddenAliases = [];

    protected array $overriddenIcons = [];

    final public function registerIcons()
    {
        $styleEnum = $this->currentStyle
            ?? $this->defaultStyle
            ?? IconStyle::from(array_key_first($this->styleMap));

        $icons = collect($this->iconMap)
            ->mapWithKeys(function (string $icon, string|BackedEnum $alias) use ($styleEnum) {
                $aliasEnum = $alias instanceof BackedEnum ? $alias : Icon::from($alias);

                $forcedStyleEnum = $this->forcedStyles[$aliasEnum->value] ?? null;
                $chosenStyleEnum = $forcedStyleEnum ?? $styleEnum;

                $styleString = $this->overriddenAliases[$aliasEnum->value]
                    ?? $this->overriddenIcons[$icon]
                    ?? $this->styleMap[$chosenStyleEnum->value]
                    ?? '';

                return [
                    $aliasEnum->value => $this->shouldPrefixStyle
                        ? $styleString.$icon
                        : $icon.$styleString,
                ];
            })
            ->toArray();

        FilamentIcon::register($icons);
    }

    final public function overrideStyleForAlias(array|BackedEnum $keys, BackedEnum $style): static
    {
        $this->setOverriddenStyle($keys, $style, 'aliases');

        return $this;
    }

    final public function overrideStyleForIcon(array|string $icons, BackedEnum $style): static
    {
        $this->setOverriddenStyle($icons, $style, 'icons');

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function setOverriddenStyle(array|string|BackedEnum $items, BackedEnum $style, string $type = 'aliases'): void
    {
        $items = is_array($items) ? $items : [$items];
        $overrideType = $type === 'aliases' ? 'overriddenAliases' : 'overriddenIcons';

        if (! array_key_exists($style->value, $this->styleMap)) {
            throw new \InvalidArgumentException("Style '{$style->value}' is not available for this icon set.");
        }

        foreach ($items as $item) {
            $key = $item instanceof BackedEnum ? $item->value : $item;
            $this->{$overrideType}[$key] = $this->styleMap[$style->value];
        }
    }

    public function __call($name, $arguments): static
    {
        if (! array_key_exists($name, $this->styleMap)) {
            throw new \InvalidArgumentException("Style '{$name}' is not available for this icon set.");
        }

        $this->currentStyle = IconStyle::from($name);

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Filament
    |--------------------------------------------------------------------------
    */
    final public function getId(): string
    {
        return $this->pluginId;
    }

    final public function boot(Panel $panel): void
    {
        static::registerIcons();
    }

    final public static function make(): static
    {
        return app(static::class);
    }

    public function register(Panel $panel): void
    {
        //
    }
}
