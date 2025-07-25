<?php

namespace Filafly\Icons;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Facades\FilamentIcon;

/**
 * Abstract base class for creating Filament icon sets with enum-driven icon mapping.
 *
 * This class provides a unified interface for replacing Filament's default icons
 * with custom icon sets. Icon sets should extend this class and:
 *
 * 1. Define an icon enum with cases like IconName::SearchRegular, IconName::SearchSolid
 * 2. Set the $pluginId, $iconEnum, and $iconPrefix (if necessary) properties
 * 3. Map Filament aliases to specific enum cases in $iconMap
 *
 * The enum-driven approach ensures type safety and eliminates runtime style resolution.
 * The iconPrefix is prepended to enum values when registering with Blade Icons
 * (e.g., prefix "carbon" + enum "translate" = "carbon-translate").
 */
abstract class IconSet implements Plugin
{
    /*
    |--------------------------------------------------------------------------
    | Set configuration
    |--------------------------------------------------------------------------
    */

    /** The unique plugin identifier for this icon set */
    protected string $pluginId;

    /** The icon enum class containing all icon cases with styles baked in */
    protected mixed $iconEnum;

    /** The prefix to prepend to icon enum values when registering with Blade Icons */
    protected string $iconPrefix;

    /** Map of Filament aliases to specific icon enum cases */
    protected array $iconMap = [];

    /*
    |--------------------------------------------------------------------------
    | Icon Overrides
    |--------------------------------------------------------------------------
    */

    /** Override specific aliases to use different icon enum cases */
    protected array $aliasOverrides = [];

    /** Override icon enum cases to be replaced with different cases */
    protected array $iconOverrides = [];

    /** Current style to apply to all icons (optional) */
    protected ?IconStyle $currentStyle = null;

    /** Available styles for this icon set (subset of IconStyle cases) */
    protected array $availableStyles = [];

    public function getIconEnum(): mixed
    {
        return $this->iconEnum;
    }

    final public function registerIcons()
    {
        $icons = collect($this->iconMap)
            ->mapWithKeys(function ($iconCase, $alias) {
                // Apply any overrides - alias overrides take precedence
                $finalIconCase = $this->aliasOverrides[$alias]
                    ?? $this->iconOverrides[$iconCase->value]
                    ?? $this->applyStyleTransformation($iconCase)
                    ?? $iconCase;

                // Prepend the icon prefix for Blade Icons
                $prefix = $this->iconPrefix ?? strtolower(class_basename($this->iconEnum));
                $iconString = $prefix.'-'.$finalIconCase->value;

                return [$alias => $iconString];
            })
            ->toArray();

        FilamentIcon::register($icons);
    }

    final public function overrideAlias(string $alias, mixed $iconCase): static
    {
        $this->aliasOverrides[$alias] = $iconCase;

        return $this;
    }

    final public function overrideAliases(array $overrides): static
    {
        foreach ($overrides as $alias => $iconCase) {
            $this->aliasOverrides[$alias] = $iconCase;
        }

        return $this;
    }

    final public function overrideIcon(mixed $fromIconCase, mixed $toIconCase): static
    {
        $this->iconOverrides[$fromIconCase->value] = $toIconCase;

        return $this;
    }

    final public function overrideIcons(array $overrides): static
    {
        foreach ($overrides as $fromIconCase => $toIconCase) {
            $fromKey = is_object($fromIconCase) ? $fromIconCase->value : $fromIconCase;
            $this->iconOverrides[$fromKey] = $toIconCase;
        }

        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Style Transformation
    |--------------------------------------------------------------------------
    */

    /**
     * Apply current style transformation to an icon case
     */
    private function applyStyleTransformation(mixed $iconCase): ?object
    {
        if (! $this->currentStyle) {
            return null;
        }

        return $this->findIconWithStyle($iconCase, $this->currentStyle);
    }

    /**
     * Find an icon enum case that matches the base name with a different style
     */
    private function findIconWithStyle(mixed $iconCase, IconStyle $targetStyle): ?object
    {
        // Check if the target style is available for this icon set
        if (! in_array($targetStyle, $this->availableStyles)) {
            return null;
        }

        $baseName = $this->extractBaseName($iconCase);
        $targetSuffix = $targetStyle->getEnumSuffix();
        $targetCaseName = $baseName.$targetSuffix;

        $enumClass = $this->getIconEnum();

        // Try to find the case with the target style
        foreach ($enumClass::cases() as $case) {
            if ($case->name === $targetCaseName) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Extract the base icon name from an enum case (removes style suffix)
     */
    private function extractBaseName(mixed $iconCase): string
    {
        $caseName = $iconCase->name;

        // Use available style suffixes
        foreach ($this->availableStyles as $style) {
            $styleSuffix = $style->getEnumSuffix();
            if (str_ends_with($caseName, $styleSuffix)) {
                return substr($caseName, 0, -strlen($styleSuffix));
            }
        }

        return $caseName;
    }

    /**
     * Set a style for all icons
     */
    public function style(string $style): static
    {
        $styleEnum = IconStyle::fromStyleName($style);

        if (! $styleEnum || ! in_array($styleEnum, $this->availableStyles)) {
            $availableStyleNames = array_map(fn ($s) => $s->getStyleName(), $this->availableStyles);
            throw new \InvalidArgumentException("Style '{$style}' is not available for this icon set. Available styles: ".implode(', ', $availableStyleNames));
        }

        $this->currentStyle = $styleEnum;

        return $this;
    }

    /**
     * Handle dynamic style method calls
     */
    public function __call(string $name, array $_): static
    {
        // Check if the method name matches an available style
        $styleEnum = IconStyle::fromStyleName($name);
        if ($styleEnum && in_array($styleEnum, $this->availableStyles)) {
            return $this->style($name);
        }

        $availableStyleNames = array_map(fn ($s) => $s->getStyleName(), $this->availableStyles);
        throw new \BadMethodCallException("Method '{$name}' does not exist. Available style methods: ".implode(', ', $availableStyleNames));
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
