<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Huxtable\Core\Config;

class Bot
{
	/**
	 * @var	Huxtable\Bot\Corpora
	 */
	protected $corpora;

	/**
	 * @var	Huxtable\Bot\Flickr\Flickr
	 */
	protected $flickr;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @var	Huxtable\Bot\Output
	 */
	protected $output;

	/**
	 * @var	string
	 */
	protected $prefix;

	/**
	 * @var	Huxtable\Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @param	string					$name			Bot name
	 * @param	string					$prefix			Bot prefix (ex., for environment variables)
	 * @param	Huxtable\Bot\History	$history
	 * @param	Huxtable\Bot\Output		$output
	 * @param	Huxtable\Core\Config	$config
	 * @param	Huxtable\Bot\Corpora	$corpora
	 * @return	void
	 */
	public function __construct( $name, $prefix, History $history, Output $output, Config $config, Corpora $corpora = null )
	{
		$this->name = $name;
		$this->prefix = $prefix;
		$this->history = $history;
		$this->output = $output;
		$this->config = $config;
		$this->corpora = $corpora;
	}

	/**
	 * @param	string		$name
	 * @param	boolean		$required
	 * @return	string
	 */
	static public function getEnvironmentVariable( $name, $required=true )
	{
		$value = getenv( $name );

		if( $value === false && $required )
		{
			throw new \Exception( "Missing environment variable '{$name}'" );
		}

		return $value;
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
		$this->flickr = new Flickr\Flickr( $flickrToken, $this->history, $this->output );

		return $this->flickr;
	}

	/**
	 * @return	Bot\Twitter
	 */
	protected function getTwitterObject()
	{
		if( $this->twitter instanceof Twitter )
		{
			return $this->twitter;
		}

		$twitterTokens = self::getEnvironmentVariable( "{$this->prefix}_TWITTER" );
		$credentialsPieces = explode( ',', $twitterTokens );

		if( count( $credentialsPieces ) != 4 )
		{
			throw new \Exception( "Missing environment variable '{$this->prefix}_TWITTER'" );
		}

		$credentials['consumerKey'] 	= $credentialsPieces[0];
		$credentials['consumerSecret']	= $credentialsPieces[1];
		$credentials['token'] 			= $credentialsPieces[2];
		$credentials['tokenSecret'] 	= $credentialsPieces[3];

		$this->twitter = new Twitter( $credentials );

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
}
