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
	 * @var	Huxtable\Bot\Corpora
	 */
	protected $corpora;

	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $dirData;

	/**
	 * @var	Huxtable\Bot\Flickr\Flickr
	 */
	protected $flickr;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @var	Huxtable\Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @param	string					$name			Bot name
	 * @param	Huxtable\Bot\History	$history
	 * @param	Huxtable\Core\Config	$config
	 * @return	void
	 */
	public function __construct( $name, History $history, Config $config )
	{
		$this->name = $name;
		$this->history = $history;
		$this->config = $config;
	}

	/**
	 * @return	Huxtable\Core\File\Directory
	 */
	public function getDataDirectory()
	{
		return $this->dirData;
	}

	/**
	 * @return	Bot\Flickr\Flickr
	 */
	public function getFlickrObject()
	{
		if( $this->flickr instanceof Flickr\Flickr )
		{
			return $this->flickr;
		}

		$flickrToken = $this->config->getValue( 'flickr', 'apiKey' );
		$this->flickr = new Flickr\Flickr( $flickrToken, $this->history );

		return $this->flickr;
	}

	/**
	 * @return	Bot\Twitter
	 */
	protected function getTwitterObject()
	{
		if( !($this->twitter instanceof Twitter) )
		{
			$credentials = $this->config->getDomain( 'twitter' );
			$this->twitter = new Twitter\Twitter( $credentials );
		}

		return $this->twitter;
	}

	/**
	 * @param	string	$message
	 * @return	void
	 */
	public function postMessageToTwitter( $message )
	{
		$twitter = $this->getTwitterObject();
		$twitter->postMessage( $message );
	}

	/**
	 * @param	Huxtable\Core\File\Directory	$dirData
	 * @return	void
	 */
	public function setDataDirectory( File\Directory $dirData )
	{
		$this->dirData = $dirData;
	}
}
