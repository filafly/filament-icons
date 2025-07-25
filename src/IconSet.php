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

    /** The style enum class containing all available styles for this icon set */
    protected mixed $styleEnum = null;

    /** Current style to apply to all icons (optional) */
    protected mixed $currentStyle = null;

    public function getIconEnum(): mixed
    {
        return $this->iconEnum;
    }

    /**
     * Get the style enum class for this icon set
     */
    public function getStyleEnum(): ?string
    {
        return $this->styleEnum;
    }

    /**
     * Get the available styles for this icon set
     */
    public function getAvailableStyles(): array
    {
        if (! $this->styleEnum) {
            return [];
        }

        return $this->styleEnum::cases();
    }

    /**
     * Get the available style names (lowercase) for this icon set
     */
    public function getAvailableStyleNames(): array
    {
        if (! $this->styleEnum) {
            return [];
        }

        return $this->styleEnum::getStyleNames();
    }

    /**
     * Check if a style is available for this icon set
     */
    public function hasStyle(string|object $style): bool
    {
        if (! $this->styleEnum) {
            return false;
        }

        if (is_string($style)) {
            return $this->styleEnum::fromStyleName($style) !== null;
        }

        return in_array($style, $this->styleEnum::cases());
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
    private function findIconWithStyle(mixed $iconCase, mixed $targetStyle): ?object
    {
        // Check if the target style is available for this icon set
        if (! $this->hasStyle($targetStyle)) {
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

        if (! $this->styleEnum) {
            return $caseName;
        }

        // Use available style suffixes from the style enum
        foreach ($this->styleEnum::cases() as $style) {
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
        if (! $this->styleEnum) {
            throw new \InvalidArgumentException('No style enum configured for this icon set.');
        }

        if (! $this->hasStyle($style)) {
            $availableStyleNames = $this->getAvailableStyleNames();
            throw new \InvalidArgumentException("Style '{$style}' is not available for this icon set. Available styles: ".implode(', ', $availableStyleNames));
        }

        $this->currentStyle = $this->styleEnum::fromStyleName($style);

        return $this;
    }

    /**
     * Handle dynamic style method calls
     */
    public function __call(string $name, array $arguments): static
    {
        // Check if the method name matches an available style
        if ($this->hasStyle($name)) {
            return $this->style($name);
        }

        $availableStyleNames = $this->getAvailableStyleNames();
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
