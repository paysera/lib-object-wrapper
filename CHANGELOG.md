# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.4.1
### Fixed
- Suppress Symfony deprecations

## 0.4.0

### Added

- PHP 8.2 compatibility

## 0.3.1

### Fixed

- Try to extract boolean value from non-boolean type values if expected type is a boolean.

## 0.3.0

### Added

- Support for php 8.0

### Removed

- Removed (temporary) paysera/lib-php-cs-fixer-config

## 0.2.2

### Fixed
- Saves originalData and introduces method `getOriginalData` to access it; Adds method `getDataAsArray` to get data in array form
- remove clone
- improve array handling in getObjectWrapperAsArray
- improve array handling in getObjectWrapperAsArray once more

## 0.2.1

### Fixed
- improve array handling in getObjectWrapperAsArray

## 0.2.0

### Added
- Ability to access originally passed data

## 0.1.1

### Fixed
- Fixed mistype for exception message

## 0.1.0

### Fixed
- Fixes strange test case with PHP 7.0
