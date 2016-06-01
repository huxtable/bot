<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

class Bot
{
	/**
	 * @var	Bot\Twitter
	 */
	protected $twitter;

	/**
	 * @param	string	$prefix		Bot prefix (ex., for environment variables)
	 * @return	void
	 */
	public function __construct( $prefix )
	{
		$this->prefix = $prefix;
	}

	/**
	 * @return	void
	 */
	protected function getTwitterObject()
	{
		if( !is_null( $this->twitter ) )
		{
			return $this->twitter;
		}

		$environmentVars[Twitter::TOKEN] 			= "{$this->prefix}_TWITTER_TOKEN";
		$environmentVars[Twitter::TOKEN_SECRET]		= "{$this->prefix}_TWITTER_TOKEN_SECRET";
		$environmentVars[Twitter::CONSUMER_KEY]		= "{$this->prefix}_TWITTER_CONSUMER_KEY";
		$environmentVars[Twitter::CONSUMER_SECRET]	= "{$this->prefix}_TWITTER_CONSUMER_SECRET";

		$twitterCredentials['token'] 			= getenv( $environmentVars[Twitter::TOKEN] );
		$twitterCredentials['tokenSecret'] 		= getenv( $environmentVars[Twitter::TOKEN_SECRET] );
		$twitterCredentials['consumerKey'] 		= getenv( $environmentVars[Twitter::CONSUMER_KEY] );
		$twitterCredentials['consumerSecret']	= getenv( $environmentVars[Twitter::CONSUMER_SECRET] );

		// If any of the credentials values are empty, Twitter will throw an exception
		try
		{
			$this->twitter = new Twitter( $twitterCredentials );
		}
		// Missing a required field
		catch( \InvalidArgumentException $e )
		{
			print_r( $e );
			exit;
		}
		// Invalid credential value detected
		catch( \Exception $e )
		{
			$invalidEnvironmentVar = $environmentVars[$e->getCode()];
			throw new \Exception( "Set environment variable '{$invalidEnvironmentVar}'." );
		}

		return $this->twitter;
	}

	/**
	 * @param	string	$message
	 * @param	string	$channel
	 * @return	void
	 */
	public function postMessageToSlack( $message, $channel='' )
	{
		$slack = $this->getSlackObject();
		$slack->post
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
