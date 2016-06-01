<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

$pathBase	= __DIR__;
$pathSrc	= $pathBase . '/src/Bot';
$pathVendor	= $pathBase . '/vendor';

/*
 * Initialize autoloading
 */
include_once( $pathSrc . '/Autoloader.php' );
Autoloader::register();

include_once( $pathVendor . '/twitteroauth/autoload.php' );
