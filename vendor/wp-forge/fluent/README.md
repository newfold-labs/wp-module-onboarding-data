# Fluent Class

A PHP utility class which allows for more flexible ways of getting and setting data.

## Installation

```shell
composer require wp-forge/fluent
```

## Usage

- [Setting Values](#setting-values)
- [Getting Values](#getting-values)
- [Checking if a Key Exists](#checking-if-a-key-exists)
- [Deleting Values](#deleting-values)

### Setting Values

Populate data from an existing array, object, or iterable

```php
<?php

use WP_Forge\Fluent\Fluent;

// Populate using an array
$fluent = new Fluent( [ 'a' => 1 ] );

var_dump( $fluent->toJson() ); // {"a":1}

// Populate using an object
$fluent = new Fluent( (object) [ 'b' => 2 ] );

var_dump( $fluent->toJson() ); // {"b":2}

// Populate using an iterable
$fluent = new Fluent( (function () { yield 1; yield 2; })() );

var_dump( $fluent->toJson() ); // [1,2]
```

Set values using array syntax

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent();

$fluent['isActive'] = true;

var_dump( $fluent->toJson() ); // {"isActive":true}
```

Set values using property syntax

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent();

$fluent->isActive = true;

var_dump( $fluent->toJson() ); // {"isActive":true}
```

Set values using the `set()` method

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent();

$fluent->set('isActive', true);

var_dump( $fluent->toJson() ); // {"isActive":true}
```

Set values using by calling non-existent methods

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent();

$fluent->isActive(); // Will set to true by default

var_dump( $fluent->toJson() ); // {"isActive":true}

$fluent->isActive( false );

var_dump( $fluent->toJson() ); // {"isActive":false}
```

### Getting Values

Get values using array syntax

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent( ['isActive' => true] );

var_dump( $fluent['isActive'] ); // true
```

Get values using property syntax

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent( ['isActive' => true] );

var_dump( $fluent->isActive ); // true
```

Get values using the `get()` method

```php
<?php

$fluent = new \WP_Forge\Fluent\Fluent( ['isActive' => true] );

var_dump( $fluent->get( 'isActive' ) ); // true

// Since "isRegistered" doesn't exist, it returns the defined default value instead
var_dump( $fluent->get( 'isRegistered', 'Ask again later' ) ); // Ask again later
```

Fetch all data using special methods

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

$fluent->toArray(); // Returns all data as an array
$fluent->toJson(); // Returns all data as JSON
```

### Checking if a Key Exists

Using array syntax

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

var_dump( isset( $fluent['a'] ) ); // true
var_dump( isset( $fluent['b'] ) ); // false
```

Using property syntax

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

var_dump( isset( $fluent->a ) ); // true
var_dump( isset( $fluent->b ) ); // false
```

Using the `has()` method

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

var_dump( $fluent->has( 'a' ) ); // true
var_dump( $fluent->has( 'b' ) ); // false
```

### Deleting Values

Using array syntax

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

unset( $fluent['a'] );

var_dump( $fluent->toJson() ); // []
```

Using property syntax

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

unset( $fluent->a );

var_dump( $fluent->toJson() ) ); // []
```

Using the `delete()` method

```php
<?php

use WP_Forge\Fluent\Fluent;

$fluent = new Fluent( ['a' => 1] );

$fluent->delete( 'a' );

var_dump( $fluent->toJson() ); // []
```
