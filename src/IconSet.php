<?php

namespace Filafly\FilamentIcons;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;

abstract class IconSet implements Plugin
{
    protected string $pluginId;

    protected string $style;

    protected array $overriddenAliases = [];

    protected array $overriddenIcons = [];

    /*
    |--------------------------------------------------------------------------
    | Icon Swap
    |--------------------------------------------------------------------------
    */
    abstract protected function getIconMap(): array;

    abstract public function getAvailableStyles(): array;

    final public function setStyle(string $style): self
    {
        $this->style = $this->getAvailableStyles()[$style];

        return $this;
    }

    final public function registerIcons()
    {
        // dump(
        //     $this->overriddenAliases,
        //     $this->overriddenIcons,
        //     $this->style
        // );

        FilamentIcon::register(
            collect($this->getIconMap())
                ->mapWithKeys(function ($icon, $key) {
                    $style = $this->overriddenAliases[$key]
                        ?? $this->overriddenIcons[$icon]
                        ?? $this->style;
                    $style ??= '';

                    return [$key => $icon.$style];
                })
                ->toArray()
        );
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
    final private function setOverriddenStyle(array|string $items, string $style, string $type = 'aliases'): void
    {
        $items = is_array($items) ? $items : [$items];
        $overrideType = $type === 'aliases' ? 'overriddenAliases' : 'overriddenIcons';

        foreach ($items as $item) {
            $this->{$overrideType}[$item] = $style === 'regular' ? '' : '-'.$style;
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

    final public function register(Panel $panel): void
    {
        //
    }
}
