# wp-module-context
A module to determine context for various brands and platforms.

## Integration
Hook into the `newfold/context/set` action to set context from another module or plugin. 

An example setting a brand with sub brand and region included.
```php
add_action(
    'newfold/context/set',
    function () {
 		setContext( 'brand.name', 'hostgator' );
 		setContext( 'brand.sub', 'latam' );
 		setContext( 'brand.region', 'BR' );
	}
);
```