<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{
	/**
	 * @var	Abraham\TwitterOAuth\TwitterOAuth
	 */
	protected $connection;

	/**
	 * @param	array	$credentials
	 * @return	void
	 */
	public function __construct( array $credentials )
	{
		$requiredKeys = ['consumerKey','consumerSecret','token','tokenSecret'];
		foreach( $requiredKeys as $requiredKey )
		{
			if( !isset( $credentials[$requiredKey] ) )
			{
				throw new \InvalidArgumentException( "Missing required Twitter credentials key '{$requiredKey}'." );
			}
		}

		extract( $credentials );
		$this->connection = new TwitterOAuth( $consumerKey, $consumerSecret, $token, $tokenSecret );
	}

	/**
	 * @param	string	$message
	 * @return	void
	 */
	public function postMessage( $message )
	{
		$this->connection->post( 'statuses/update', ['status' => $message] );

		if( ($errorCode = $this->connection->getLastHttpCode()) != 200 )
		{
			throw new \Exception( "There was a problem posting to Twitter ({$errorCode})" );
		}
	}
}
