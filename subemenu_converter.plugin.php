<?php
/**
 * Plugin Name: Submenu Converter
 * Plugin URI: https://github.com/drajathasan/slims-submenu-converter
 * Description: Konversi Submenu lama ke baru
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */
use SLiMS\Plugins;

Plugins::menu('system', 'Pengkonversi Submenu', __DIR__ . '/index.php');