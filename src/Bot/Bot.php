<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

class Bot
{
	/**
	 * @var	Bot\Flickr\Flickr
	 */
	protected $flickr;

	/**
	 * @var	string
	 */
	protected $name;

	/**
	 * @var	string
	 */
	protected $prefix;

	/**
	 * @var	Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @param	string	$name		Bot name
	 * @param	string	$prefix		Bot prefix (ex., for environment variables)
	 * @return	void
	 */
	public function __construct( $name, $prefix, History $history )
	{
		$this->name = $name;
		$this->prefix = $prefix;
		$this->history = $history;
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

		$varName = "{$this->prefix}_FLICKR";
		$flickrToken = getenv( $varName );

		if( $flickrToken === false )
		{
			throw new \Exception( "Missing environment variable '{$varName}'" );
		}

		$this->flickr = new Flickr\Flickr( $flickrToken );
		$this->flickr = new Flickr\Flickr( $flickrToken, $this->history );

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

		$envTwitter = getenv( "{$this->prefix}_TWITTER" );
		$credentialsPieces = explode( ',', $envTwitter );

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
