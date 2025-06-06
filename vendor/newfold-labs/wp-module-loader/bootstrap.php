<?php

if ( function_exists( 'add_action' ) ) {

	add_action( 'after_setup_theme', 'NewfoldLabs\\WP\\ModuleLoader\\load', 100 );

}
