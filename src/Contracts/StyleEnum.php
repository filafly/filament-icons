<?php

namespace Filafly\Icons\Contracts;

/**
 * Interface that style enums must implement to be used with IconSet.
 *
 * This ensures that custom style enums provide the necessary methods
 * for integration with the IconSet style system.
 */
interface StyleEnum
{
    /**
     * Get the style name (lowercase) for method calls
     */
    public function getStyleName(): string;

    /**
     * Get the enum suffix for this style (used in icon enum case names)
     */
    public function getEnumSuffix(): string;

    /**
     * Get all available style names for error messages
     */
    public static function getStyleNames(): array;

    /**
     * Find a style case by its style name
     */
    public static function fromStyleName(string $styleName): mixed;

    /**
     * Get all enum cases (required for PHP enums)
     */
    public static function cases(): array;
}
