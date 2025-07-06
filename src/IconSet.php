<?php

namespace Filafly\Icons;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;

abstract class IconSet implements Plugin
{
    /*
    |--------------------------------------------------------------------------
    | Set configuration
    |--------------------------------------------------------------------------
    */
    protected string $pluginId;

    protected mixed $iconEnum;

    protected mixed $styleEnum;

    protected mixed $defaultStyle;

    protected bool $shouldPrefixStyle = false;

    protected array $styleMap = [];

    protected array $iconMap = [];

    protected array $forcedStyles = [];

    /*
    |--------------------------------------------------------------------------
    | Icon Swap
    |--------------------------------------------------------------------------
    */
    protected mixed $currentStyle;

    protected array $overriddenAliases = [];

    protected array $overriddenIcons = [];

    public function getStyleEnum(): mixed
    {
        return $this->styleEnum;
    }

    public function getIconEnum(): mixed
    {
        return $this->iconEnum;
    }

    final public function registerIcons()
    {
        $style = $this->currentStyle ?? $this->defaultStyle ?? $this->getStyleEnum()::cases()[0];

        $icons = collect($this->iconMap)
            ->mapWithKeys(function ($icon, $key) use ($style) {
                $forcedStyle = $this->forcedStyles[$icon] ?? null;
                $chosenStyle = $forcedStyle ?? $style;

                $styleString = $this->overriddenAliases[$key]
                    ?? $this->overriddenIcons[$icon]
                    ?? $chosenStyle->value
                    ?? '';

                return [$key => $this->shouldPrefixStyle
                    ? $styleString.$icon
                    : $icon.$styleString];
            })
            ->toArray();

        FilamentIcon::register($icons);
    }

    final public function overrideStyleForAlias(array|string $keys, string $style): static
    {
        $this->setOverriddenStyle($keys, $style, 'aliases');

        return $this;
    }

    final public function overrideStyleForIcon(array|string $icons, string $style): static
    {
        $this->setOverriddenStyle($icons, $style, 'icons');

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function setOverriddenStyle(array|string $items, string $style, string $type = 'aliases'): void
    {
        $items = is_array($items) ? $items : [$items];
        $overrideType = $type === 'aliases' ? 'overriddenAliases' : 'overriddenIcons';

        if (! array_key_exists($style, $this->styleMap)) {
            throw new \InvalidArgumentException("Style '{$style}' is not available for this icon set.");
        }

        foreach ($items as $item) {
            $this->{$overrideType}[$item] = $this->styleMap[$style];
        }
    }

    public function __call($name, $arguments): static
    {
        if (! array_key_exists($name, $this->styleMap)) {
            throw new \InvalidArgumentException("Style '{$name}' is not available for this icon set.");
        }

        $this->currentStyle = $name;

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
