# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Core Architecture

This is a PHP library that provides a unified interface for replacing Filament's default Heroicons with custom icon sets. The package uses Laravel/Filament patterns and provides a plugin system for icon management.

### Key Components

- **IconSet.php** (`src/IconSet.php`): Abstract base class that implements Filament's Plugin interface. Handles icon registration, style management, and provides fluent API for icon configuration.
- **Icon Enum System**: Uses PHP enums with all icon+style combinations baked in (e.g., SearchRegular, SearchSolid)
- **Plugin Architecture**: Integrates with Filament panels as a plugin using Laravel's service container

### Design Patterns

- **Enum-driven approach**: Icons with styles are defined as PHP enum cases implementing `ScalableIcon` interface
- **Fluent API**: Chainable methods for configuration (e.g., `->overrideAlias()`, `->overrideIcon()`)
- **Plugin pattern**: Extends Filament's plugin system with `boot()` and `register()` methods
- **Icon Prefix System**: Automatic prepending of icon prefixes for Blade Icons compatibility

## Development Commands

### Code Quality
```bash
# Format code using Laravel Pint
vendor/bin/pint
```

### Package Management
```bash
# Install dependencies
composer install

# Update dependencies
composer update
```

## Implementation Guidelines

### Implementation Approach

When creating new icon set implementations:

1. **Icon Enum**: Create a single enum with all icon+style combinations (e.g., `SearchRegular`, `SearchSolid`)
2. **IconSet Extension**: Extend the base `IconSet` class with pattern `{Name}Icons`
3. **Required Properties**: Set `$pluginId`, `$iconEnum`, and `$iconPrefix`
4. **Icon Mapping**: Map Filament aliases to specific enum cases in `$iconMap` array
5. **Prefix Handling**: The `$iconPrefix` is automatically prepended to enum values (e.g., "carbon-translate")

### Override System

- **Alias Overrides**: Use `overrideAlias(string $alias, mixed $iconCase)` to override specific aliases
- **Icon Overrides**: Use `overrideIcon(mixed $fromIconCase, mixed $toIconCase)` to replace enum cases
- **Bulk Operations**: Use `overrideAliases(array)` and `overrideIcons(array)` for multiple overrides
- **Override Precedence**: Alias overrides > Icon overrides > Default mapping

### Upgrading from v1.x

If upgrading from the previous style-based approach, see the upgrade guide in README.md for detailed migration steps from the old `$styleMap` approach to the new enum-driven system.

## File Structure

```
src/
├── IconSet.php          # Core abstract class with plugin implementation
composer.json            # PHP dependencies and package metadata
README.md               # Package documentation and usage examples
```

This is a core/base package - actual icon implementations are separate packages that depend on this one.