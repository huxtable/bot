<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

$pathBaseBot	= __DIR__;
$pathSrcBot		= $pathBaseBot . '/src/Bot';
$pathVendorBot	= $pathBaseBot . '/vendor';

/*
 * Initialize autoloading
 */
include_once( $pathSrcBot . '/Autoloader.php' );
Autoloader::register();

include_once( $pathVendorBot . '/twitteroauth/autoload.php' );
