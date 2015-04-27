<?php
/**
 * Plugin Name: IndexData
 * Plugin URI: http://fruitware.ru
 * Description: Indexdata integration and widget
 * Version: 0.0.1
 * Author: Fruitware
 * Author URI: http://fruitware.ru
 * Text Domain: wp-indexdata
 * Domain Path: /locale/
 * License: GPLv2 or later
 */

define('INDEXDATA_LANG_NAMESPACE', 'wp-indexdata');
define('INDEXDATA_PLUGIN_URI', plugin_dir_url( __FILE__ ));

require_once 'libs/widget.php';
require_once 'libs/plugin.php';

WordpressIndexData\Plugin::init();