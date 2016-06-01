<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter
{
	const TOKEN = 0;
	const TOKEN_SECRET = 1;
	const CONSUMER_KEY = 2;
	const CONSUMER_SECRET = 4;

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
		$errorCodes['token'] = self::TOKEN;
		$errorCodes['tokenSecret'] = self::TOKEN_SECRET;
		$errorCodes['consumerKey'] = self::CONSUMER_KEY;
		$errorCodes['consumerSecret'] = self::CONSUMER_SECRET;

		$requiredKeys = ['token','tokenSecret','consumerKey','consumerSecret'];

		foreach( $requiredKeys as $requiredKey )
		{
			if( !isset( $credentials[$requiredKey] ) )
			{
				throw new \InvalidArgumentException( "Missing required argument '{$requiredKey}'." );
			}

			if( $credentials[$requiredKey] === false )
			{
				throw new \Exception( "Invalid '{$requiredKey}' value", $errorCodes[$requiredKey] );
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
