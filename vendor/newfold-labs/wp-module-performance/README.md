<a href="https://newfold.com/" target="_blank">
    <img src="https://newfold.com/content/experience-fragments/newfold/site-header/master/_jcr_content/root/header/logo.coreimg.svg/1621395071423/newfold-digital.svg" alt="Newfold Logo" title="Newfold Digital" align="right" 
height="42" />
</a>

# WordPress Performance Module
[![Version Number](https://img.shields.io/github/v/release/newfold-labs/wp-module-performance?color=21a0ed&labelColor=333333)](https://github.com/newfold/wp-module-performance/releases)
[![License](https://img.shields.io/github/license/newfold-labs/wp-module-performance?labelColor=333333&color=666666)](https://raw.githubusercontent.com/newfold-labs/wp-module-performance/master/LICENSE)

A module for managing caching functionality.

## Module Responsibilities

- The performance module handles the following types of performance improvements:
    - **Browser caching** - Sets the appropriate browser caching rules in the `.htaccess` file based on the cache level the user selects.
    - **Cloudflare cache clearing** - If Cloudflare is enabled, send a cache clear request when a programmatic or user-initiated cache purge is requested.
    - **File-based caching** - This is responsible for generating static HTML files that can be served on sites that don't have dynamic content.
    - **Nginx reverse proxy cache clearing** - Responsible for sending a cache clear request to our reverse proxy when requested by a programmatic or user-initiated cache purge.
    - **Sitelock cache purging** - If Sitelock is enabled, send a cache clear request when a programmatic or user-initiated cache purge is requested.
    - **Skipping 404 handling for static files** - If enabled, this setting sets a rule in the `.htaccess` file to prevent all of WordPress from loading if a static file is not found. It applies to HTML, CSS, JS, and media files such as images, videos, and documents.
- Not all brand plugins utilize all performance improvements. It depends on what performance features a given hosting brand supports. Each plugin registers the required performance options in the shared dependency injection container.
- Users can control the caching level, which only impacts browser and file-based caching.
- Users can toggle the 'Skip 404 handling for static files' option.
- Users can use the 'Purge All' and 'Purge This Page' options in the WordPress admin bar to initiate a purge request.
- The cache purging service ensures that all active types of caching are purged when a purge request is made. The user can make purge requests, or a purge might happen when certain events happen in WordPress. For example, when a post changes status, that page is purged from the cache. Updates to a nav menu will result in a full site purge request.
- The response header manager uses `.htaccess` to set a `X-Newfold-Cache-Level` header for debugging purposes.

## Critical Paths

- Only performance options enabled by the plugin should be active.
- When a user updates the performance options, it should properly toggle or adjust the appropriate performance options in the database and any applicable rules in the `.htaccess` file.
- When a user initiates a cache purge request, all active and purgeable services should successfully perform a purge.
- When specific events occur in WordPress, such as updating a post or changing a menu, all active and purgeable services should successfully perform a purge for the appropriate URLs.
- Enabling a brand plugin should add rules to the `.htaccess` file based on the default caching level.
- Disabling a brand plugin should remove all rules added by the module from the `.htaccess` file.

## Installation

### 1. Add the Newfold Satis to your `composer.json`.

 ```bash
 composer config repositories.newfold composer https://newfold-labs.github.io/satis
 ```

### 2. Require the `newfold-labs/wp-module-performance` package.

 ```bash
 composer require newfold-labs/wp-module-performance
 ```

### 3. Instantiate the Features singleton to load all features.

```
Features::getInstance();
```

[More on Newfold WordPress Modules](https://github.com/newfold-labs/wp-module-loader)

[More on the Newfold Features Modules](https://github.com/newfold-labs/wp-module-features)

## TODO

- Create a cron to clear old static cached files
- Implement UI component for handling performance (and action/reducer)
