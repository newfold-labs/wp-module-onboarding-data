<a href="https://newfold.com/" target="_blank">
    <img src="https://newfold.com/content/experience-fragments/newfold/site-header/master/_jcr_content/root/header/logo.coreimg.svg/1621395071423/newfold-digital.svg" alt="Newfold Logo" title="Newfold Digital" align="right" 
height="42" />
</a>

# WordPress Onboarding Data Module
A non-toggleable module containing a standardized interface for interacting with Onboarding data.

## Module Responsibilities

- **Data and Configuration Storage**: Acts as a common central repository for configurations and default data, facilitating accessibility and reuse across diverse modules. It provides essential configurations, data, and services that are reusable across various other modules. 
- **Service Provisioning**: Offers common services that support, update and streamline processes in other modules,  particularly focusing on user onboarding and ecommerce functionalities.

## Critical Paths

- **Flow Service Integration**: Modules utilizing the Flow Service must be capable of setting and manipulating default flow data effectively. Examples of flow being default onboarding, ai site generation or ecommerce
- **Theme Generation**: The Theme Generator Service should support the creation and activation of new themes, ensuring seamless theme management.
- **Block Rendering**: The Block Render Service is responsible for rendering and capturing screenshots of blocks, aiding in block grammar visualization.
- **Site Generation**: Upon activation, the Site Gen Service should fetch AI-generated site metadata and facilitate the generation or regeneration of homepages and other site pages. 
- **Default Value Accessibility**: Ensures that all modules can access and utilize default values related to brands, themes, data patterns, and other critical elements seamlessly.

## Installation

### 1. Add the Newfold Satis to your `composer.json`.

 ```bash
 composer config repositories.newfold composer https://newfold-labs.github.io/satis
 ```

### 2. Require the `newfold-labs/wp-module-onboarding-data` package.

 ```bash
 composer require newfold-labs/wp-module-onboarding-data
 ```

[More on NewFold WordPress Modules](https://github.com/newfold-labs/wp-module-loader)
