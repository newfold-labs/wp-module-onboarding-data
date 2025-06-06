# URL Handler

[![Tests](https://github.com/wpscholar/url/actions/workflows/tests.yml/badge.svg)](https://github.com/wpscholar/url/actions/workflows/tests.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/wpscholar/url.svg)](https://packagist.org/packages/wpscholar/url)
[![PHP Version Support](https://img.shields.io/badge/php-%3E%3D7.2-blue)](https://packagist.org/packages/wpscholar/url)
[![License](https://img.shields.io/packagist/l/wpscholar/url.svg)](https://packagist.org/packages/wpscholar/url)
[![codecov](https://codecov.io/gh/wpscholar/url/branch/main/graph/badge.svg)](https://codecov.io/gh/wpscholar/url)

A PHP library for parsing, manipulating, and building URLs.

## Description

This library provides a simple and intuitive way to work with URLs in PHP. It allows you to parse, manipulate, and build URLs while handling all the common URL components including scheme, host, port, path, query parameters, and fragments.

## Features

- Parse URLs into their component parts
- Build URLs from component parts
- Add, remove, and modify query parameters
- Handle URL fragments
- Detect and manipulate trailing slashes
- Get current URL and scheme detection
- Path segment manipulation
- URL string conversion


## Requirements

- PHP 7.2 or higher

## Installation

Install via Composer:

```bash
composer require wpscholar/url
```

## Testing

This library includes a comprehensive test suite built with PHPUnit. To run the tests:

1. Install development dependencies:
```bash
composer install
```

2. Run the test suite:
```bash
composer test
```

The test suite covers all major functionality including:
- URL parsing
- Query parameter manipulation
- Static helper methods
- Path manipulation
- URL output methods

The test suite is automatically run via GitHub Actions whenever code is pushed to the `main` branch or when a pull request is created. The tests are run against multiple PHP versions (7.2, 7.3, 7.4, 8.0, 8.1, 8.2, and 8.3) to ensure broad compatibility.

## Basic Usage

```php
use wpscholar\Url;

// Create from a URL string
$url = new Url('https://example.com/path?param=value#section');

// Get the current URL
$currentUrl = new Url(); // Automatically uses current URL

// Access URL components
echo $url->scheme; // 'https'
echo $url->host; // 'example.com'
echo $url->path; // '/path'
echo $url->query; // 'param=value'
echo $url->fragment; // 'section'

// Modify query parameters
$url->addQueryVar('new_param', 'value');
$url->removeQueryVar('old_param');

// Get specific query parameter
$value = $url->getQueryVar('param_name');

// Get all query parameters as array
$params = $url->getQueryVars();
```

## Static Helpers

```php
// Get current URL
$currentUrl = Url::getCurrentUrl();

// Get current scheme (http/https)
$scheme = Url::getCurrentScheme();

// Strip query string from URL
$cleanUrl = Url::stripQueryString($url);

// Build URL from parts
$url = Url::buildUrl([
    'scheme' => 'https',
    'host' => 'example.com',
    'path' => '/path',
    'query' => 'param=value'
]);
```

## Path Manipulation

```php
// Given URL: https://example.com/blog/2023/post-title

// Get all path segments as array
$segments = $url->getSegments();
// Returns: ['blog', '2023', 'post-title']

// Get specific segment by index (zero-based)
$year = $url->getSegment(1);     // Returns: '2023'
$section = $url->getSegment(0);  // Returns: 'blog'
$slug = $url->getSegment(2);     // Returns: 'post-title'
```

## URL Output

```php
// Get full URL as string - multiple methods:
$url = new Url('https://example.com/path?param=value');

// Method 1: Using toString()
echo $url->toString();  // 'https://example.com/path?param=value'

// Method 2: Cast to string directly
echo (string) $url;     // 'https://example.com/path?param=value'

// Method 3: Using magic __toString()
echo $url;              // 'https://example.com/path?param=value'

// Get URL parts as array
$urlParts = $url->toArray();  // Returns array of URL components
```