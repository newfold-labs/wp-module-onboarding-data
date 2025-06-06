<?php

WP_Mock::activateStrictMode();
/**
 * Patchwork allows redefining PHP functions.
 *
 * @see patchwork.json
 * @see https://github.com/antecedent/patchwork
 */
WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
