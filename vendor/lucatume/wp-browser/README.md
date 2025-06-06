# wp-browser

You can use wp-browser to test WordPress sites, plugins and themes.

Version `3.5` is built from the transpiling of version `4.0` of the project.

If you're using version `~3.2`, read [the migration guide][6] to update your project to either version `3.5` or
version `4.0` depending on your PHP compatibility requirements.

## Installation

Add wp-browser to your project as a development dependency using [Composer][1]

```bash
cd my-wordrpess-project
composer require --dev lucatume/wp-browser
```

Initialize wp-browser to quickly configured to suite your project and setup:

```bash
vendor/bin/codecept init wpbrowser
```

The command will set up your project to run integration and end-to-end tests using:

* SQLite as the database engine, leveraging the [SQLite Database Integration plugin][2]
* PHP built-in web server to serve the WordPress site on localhost (e.g. `http://localhost:8080`)
* Chromedriver to drive the local version of Chrome installed on your machine

If you're working on a plugin or theme project, the default configuration will add some extra steps:

* install the latest version of WordPress in the `tests/_wordpress` directory
* create a `tests/_plugins` directory: any file or directory in this directory will be symlinked into the WordPress
  installation in `tests/_wordpress/wp-content/plugins`
* create a `tests/_themes` directory: any file or directory in this directory will be symlinked into the WordPress
  installation in `tests/_wordpress/wp-content/themes`

For most projects this configuration will be enough to get started with testing.

You can run your tests immediately using the `vendor/bin/codecept run` command.

[Read more about the commands provided by the library here.](https://wpbrowser.wptestkit.dev/commands/)

### Using a custom configuration

If you decide to skip the default configuration, you will be able to set up `wp-browser` to suit your needs and local
setup by editing the `tests/.env` file.
The inline documentation in the file will guide you through the configuration process.

[Read more about using a custom configuration here.](https://wpbrowser.wptestkit.dev/commands/)

## Getting support for wp-browser configuration and usage

The best place to get support for wp-browser is [the project documentation](https://wpbrowser.wptestkit.dev).  
Since this project builds on top of [PHPUnit][3] and [Codeception][4], you can also refer to their documentation.

If you can't find the answer to your question here you can ask on
the ["Issues" section of the wp-browser repository][5] taking care to provide as much information as possible.

Finally, you can <a href="mailto:luca@theaveragedev.com">contact me directly</a> to set up a call to discuss your
project needs and how wp-browser can help you.

## Sponsors

A thanks to my sponsors: you make maintaining this project easier.

[1]: https://getcomposer.org/

[2]: https://wordpress.org/plugins/sqlite-database-integration/

[3]: https://phpunit.de/

[4]: https://codeception.com/

[5]: https://github.com/lucatume/wp-browser/issues/new/choose

[6]: https://wpbrowser.wptestkit.dev/migration/
