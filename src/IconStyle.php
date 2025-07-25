<?php

namespace Filafly\Icons;

/**
 * Enum representing available icon styles and their corresponding enum suffixes.
 *
 * This enum defines the mapping between style names (used in method calls like ->regular())
 * and their corresponding enum case suffixes (like "Regular" in SearchRegular).
 */
enum IconStyle: string
{
    case Regular = 'Regular';
    case Solid = 'Solid';
    case Filled = 'Filled';
    case Outline = 'Outline';
    case Light = 'Light';
    case Bold = 'Bold';
    case Duotone = 'Duotone';
    case Sharp = 'Sharp';

    /**
     * Get the style name (lowercase) for method calls
     */
    public function getStyleName(): string
    {
        return strtolower($this->name);
    }

    /**
     * Get the enum suffix for this style
     */
    public function getEnumSuffix(): string
    {
        return $this->value;
    }

    /**
     * Get all available style names for error messages
     */
    public static function getStyleNames(): array
    {
        return array_map(fn ($case) => $case->getStyleName(), self::cases());
    }

    /**
     * Find a style case by its style name
     */
    public static function fromStyleName(string $styleName): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->getStyleName() === $styleName) {
                return $case;
            }
        }

        return null;
    }
}
