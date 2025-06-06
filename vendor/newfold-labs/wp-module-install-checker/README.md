<div style="text-align: center;">
 <a href="https://newfold.com/" target="_blank">
  <img src="https://newfold.com/content/experience-fragments/newfold/site-header/master/_jcr_content/root/header/logo.coreimg.svg/1621395071423/newfold-digital.svg" alt="Newfold Logo" title="Newfold Digital" height="42" />
 </a>
</div>

# WordPress Install Checker Module

A module that handles checking a WordPress installation to see if it is a fresh install and to fetch the estimated installation date.

This module uses the Newfold Module Loader to register new references into the dependency injection container. Only meant for use in Newfold brand plugins.
 
 ## Installation
 
 ### 1. Add the Newfold Satis to your `composer.json`.
 
  ```bash
 composer config repositories.newfold composer https://newfold-labs.github.io/satis
 ```
 
 ### 2. Require the `newfold-labs/wp-module-install-checker` package.
 
 ```bash
 composer require newfold-labs/wp-module-install-checker
 ```

 ## Usage
 
 ```php
 <?php

 use function NewfoldLabs\WP\ModuleLoader\container;

/**
 * Returns a boolean indicating whether this is a fresh WordPress installation.
 */
 $isFreshInstall = container()->get('isFreshInstallation');

 /**
  * Returns a Unix timestamp representing the site creation date.
  */
 $installationDate = container()->get('installationDate');

 /**
  * Returns an InstallChecker class instance.
  */
  $installChecker = container()->get('installChecker');
 ```
