<?php

namespace Filafly\Icons;

use BackedEnum;
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

    protected string $defaultStyle;

    protected bool $shouldPrefixStyle = false;

    protected array $styleMap = [];

    protected array $iconMap = [];

    protected array $forcedStyles = [];

    /*
    |--------------------------------------------------------------------------
    | Icon Swap
    |--------------------------------------------------------------------------
    */
    protected string $currentStyle;

    protected array $overriddenAliases = [];

    protected array $overriddenIcons = [];

    final public function registerIcons()
    {
        $styleEnum = $this->currentStyle ?? $this->defaultStyle ?? array_key_first($this->styleMap);

        $icons = collect($this->iconMap)
            ->mapWithKeys(function ($iconEnum, $keyEnum) use ($styleEnum) {
                $forcedStyleEnum = $this->forcedStyles[$iconEnum->value] ?? null;
                $chosenStyleEnum = $forcedStyleEnum ?? $styleEnum;

                $styleString = $this->overriddenAliases[$keyEnum->value]
                    ?? $this->overriddenIcons[$iconEnum->value]
                    ?? $this->styleMap[$chosenStyleEnum->value]
                    ?? '';

                return [
                    $keyEnum->value => $this->shouldPrefixStyle
                        ? $styleString.$iconEnum->value
                        : $iconEnum->value.$styleString,
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

    final public function overrideStyleForIcon(array|BackedEnum $icons, BackedEnum $style): static
    {
        $this->setOverriddenStyle($icons, $style, 'icons');

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function setOverriddenStyle(array|BackedEnum $items, BackedEnum $style, string $type = 'aliases'): void
    {
        $items = is_array($items) ? $items : [$items];
        $overrideType = $type === 'aliases' ? 'overriddenAliases' : 'overriddenIcons';

        if (! array_key_exists($style->value, $this->styleMap)) {
            throw new \InvalidArgumentException("Style '{$style->value}' is not available for this icon set.");
        }

        foreach ($items as $item) {
            $this->{$overrideType}[$item->value] = $this->styleMap[$style->value];
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
