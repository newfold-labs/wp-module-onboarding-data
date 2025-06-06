<a href="https://newfold.com/" target="_blank">
    <img src="https://newfold.com/content/experience-fragments/newfold/site-header/master/_jcr_content/root/header/logo.coreimg.svg/1621395071423/newfold-digital.svg" alt="Newfold Logo" title="Newfold Digital" align="right" 
height="42" />
</a>

# WordPress Survey Module

A Newfold module to collect customer satisfaction feedback via surveys in the WordPress admin dashboard.

## Module Responsibilities
- Listen to specific `data-survey-*` attributes and report events whenever a user fills out these surveys. Refer to the Usage section for more details.
- Allow other modules/plugins to queue predefined surveys and display them on the WordPress admin dashboard.
- Provide standardized (plugin-branded) UI components, but these are not mandatory for displaying a survey. Any element(s) with the correct `data-survey-*` attributes are eligible to become a survey.

## Critical Paths
- If there are HTML elements on a web page with the correct `data-survey-*` attributes as described in the usage section, when a user submits the survey, the module will submit an event to the Events API.
- If other modules/plugins have queued predefined surveys to be displayed on the next wp-admin page load, then display these surveys without issues.
- If the standardized (plugin-branded) UI components are used elsewhere, they should behave as expected and report events on submission of feedback or be dismissible.


## Installation

### 1. Add the Newfold Satis to your `composer.json`.

 ```bash
 composer config repositories.newfold composer https://newfold.github.io/satis
 ```

### 2. Require the `newfold-labs/wp-module-survey` package.

 ```bash
 composer require newfold-labs/wp-module-survey
 ```
[More on Newfold WordPress Modules](https://github.com/newfold-labs/wp-module-loader)

## Usage

### Creating a Survey

To create a survey, write HTML markup to achieve your survey goals and attach certain `data-survey-*` attributes to them. They are:

1. `data-survey-action`: A unique identifier that represents your survey goal (source). This goes on the first parent element of your survey.
2. `data-survey-category`: A unique identifier that allows you to categorize your surveys to a particular application or feature in your module/plugin. This also goes on the first parent element of your survey.
3. `data-survey-data`: Additional data that you want to report when a user submits your survey. This also goes on the first parent element of your survey.
4. `data-survey-option`: This goes on your clickable elements. Each option represents a unique value that you will use to evaluate your survey response. For example, if you have two buttons representing Yes and No, your first button will have `data-survey-option="Yes"` and your second button will have `data-survey-option="No"`.

**Note:** 
1. Missing any of these `data-survey` attributes will not guarantee that your survey will function correctly.
2. If your value is a string with multiple words, then the separator must be an `_`.

**Example**

Let's take an example to understand how we can use the survey module to gather feedback from a user.

We have a simple cache purger plugin responsible for flushing out the cache of a WordPress site. We have a business case to understand if users were happy with the results once they purged their cache. Time to use a quick survey. For this example, we will keep the code simple. The goal is to not make it look good but to give you an understanding of how the module works.

Let's say we already have the code written such that when a user purges the cache, we store an option and, on the next login to WordPress admin, we display a feedback form saying something like, "Did the caching plugin work well for you?" with simple "Yes," "No," and "I don't know" options as feedback.

```html
<div class="feedback-form" data-survey-action="cache_feedback" data-survey-category="cache_purger_plugin" data-survey-data='{"page": "plugins.php"}'>
    <h2>Did the caching plugin work well for you?</h2>
    <form id="cache-feedback-form">
        <button type="button" data-survey-option="Yes">Yes</button>
        <button type="button" data-survey-option="No">No</button>
        <button type="button" onclick="closeSurvey();">I don't know</button>
    </form>
</div>
```

Now, when a user clicks on any one of the `data-survey-option` buttons, the module listens to the onclick event and triggers an event to Hiive with the selected option.

### Queuing a Pre-defined Survey

The module exposes a set of pre-defined, well-tested branded survey components that can be used to display a standard set of surveys across the WordPress Admin dashboard. To use a pre-defined survey:

1. Include the module container in your file.
    ```php
    use function NewfoldLabs\WP\ModuleLoader\container;
    ```

2. Use the module container `survey` service to call functions that will queue the survey for display on the next admin page load. Refer to [Service.php](https://github.com/newfold-labs/wp-module-survey/blob/main/includes/Service.php) for all the available functions.
    ```php
    container()->get('survey')->create_toast_survey(
        'example_toast_survey',
        'customer_satisfaction_survey',
        array(
            'label_key' => 'value',
        ),
        __('Help us improve', 'your-text-domain'),
        __('How satisfied were you with the ease of creating your website?', 'your-text-domain')
    );
    ```
3. This queues a toast survey for display on the next WordPress admin page load with the correct `data-survey-*` attributes.
