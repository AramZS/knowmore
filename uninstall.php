<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   Know_More
 * @author    Yuri Victor <yurivictor@gmail.com>
 * @license   GPL-2.0+
 * @link      http://www.washingtonpost.com
 * @copyright 2013 Yuri Victor
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// TODO: Define uninstall functionality here