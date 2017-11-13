<?php

/**
 * Plugin Name: Site Tree View
 * Description:
 * Author: Fredrik Forsmo
 * Author URI: https://frozzare.com
 * Version: 1.0.0
 * Plugin URI:
 * Textdomain: stv
 */

require_once __DIR__ . '/custom-post-type.php';
require_once __DIR__ . '/src/class-posts.php';
require_once __DIR__ . '/src/nodes/class-node.php';
require_once __DIR__ . '/src/nodes/class-post-node.php';
require_once __DIR__ . '/src/nodes/class-post-nodes.php';
require_once __DIR__ . '/src/nodes/class-post-type-node.php';
require_once __DIR__ . '/src/class-repository.php';
require_once __DIR__ . '/src/class-plugin.php';

/**
 * Bootstra plugin.
 */
add_action( 'plugins_loaded', function () {
	WPUP\SiteTreeView\Plugin::instance();
} );
