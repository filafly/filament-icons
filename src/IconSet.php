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
                $icon = $icon->value;

                $forcedStyle = $this->forcedStyles[$icon] ?? null;
                $chosenStyle = $forcedStyle ?? $style;

                $styleString = $this->overriddenAliases[$key]->value
                    ?? $this->overriddenIcons[$icon]->value
                    ?? $chosenStyle->value;

                $iconName = $this->shouldPrefixStyle
                    ? $styleString.$icon
                    : $icon.$styleString;

                $validIcon = $this->getIconEnum()::tryFrom($iconName);
                if (! $validIcon) {
                    $iconString = $this->shouldPrefixStyle
                        ? $this->defaultStyle->value.$icon
                        : $icon.$this->defaultStyle->value;
                } else {
                    $iconString = $validIcon->value;
                }

                return [$key => $iconString];
            })
            ->toArray();

        FilamentIcon::register($icons);
    }

    final public function overrideStyleForAlias(mixed $keys, mixed $style): static
    {
        $this->setOverriddenStyle($keys, $style, 'aliases');

        return $this;
    }

    final public function overrideStyleForIcon(mixed $icons, mixed $style): static
    {
        $this->setOverriddenStyle($icons, $style, 'icons');

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function setOverriddenStyle(mixed $items, mixed $style, string $type): void
    {
        $items = is_array($items) ? $items : [$items];
        $overrideType = $type === 'aliases' ? 'overriddenAliases' : 'overriddenIcons';

        foreach ($items as $item) {
            $item = gettype($item) === 'string' ? $item : $item->value;
            $this->{$overrideType}[$item] = $style;
        }
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
