<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Config;

use Huxtable\Core\Config;
use Huxtable\Core\File;

trait Consumer
{
	/**
	 * @var	Huxtable\Core\Config
	 */
	protected $config;

	/**
	 * @return	Huxtable\Bot\Config
	 */
	public function getConfigObject()
	{
		if( $this->config instanceof \Huxtable\Core\Config )
		{
			return $this->config;
		}

		$fileConfig = $this->dirData->child( 'config.json' );
		$this->config = new Config( $fileConfig );

		return $this->config;
	}
}
