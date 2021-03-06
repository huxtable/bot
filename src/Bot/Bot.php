<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Huxtable\Core\Config;
use Huxtable\Core\File;

class Bot
{
	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $dirData;

	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $dirTemp;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @param	string							$name			Bot name
	 * @param	Huxtable\Core\File\Directory	$dirData		ex., /var/opt/<bot>
	 * @return	void
	 */
	public function __construct( $name, File\Directory $dirData )
	{
		$this->name = $name;
		$this->dirData = $dirData;

		/* Temp Directory */
		$this->dirTemp = $dirData->childDir( 'tmp' );

		if( !$this->dirTemp->exists() )
		{
			$this->dirTemp->create();
		}
	}

	/**
	 * @return	Huxtable\Core\File\Directory
	 */
	public function getDataDirectory()
	{
		return $this->dirData;
	}

	/**
	 * @return	Huxtable\Core\File\Directory
	 */
	public function getTempDirectory()
	{
		return $this->dirTemp;
	}

	/**
	 * @param
	 * @return	void
	 */
	public function postMessageToSlack( Slack\Message $message )
	{
		$webhookURL = $this->config->getValue( 'slack', 'webhook' );
		$slack = new Slack\Slack();

		/* Configure Message */
		$message->setUsername( $this->config->getValue( 'slack', 'name' ) );
		$message->setEmoji( $this->config->getValue( 'slack', 'emoji' ) );

		$response = $slack->postMessage( $webhookURL, $message );
	}
}
