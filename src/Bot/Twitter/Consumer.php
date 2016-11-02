<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Twitter;

use Huxtable\Core\File;

trait Consumer
{
	use \Huxtable\Bot\Config\Consumer;

	/**
	 * @var	Huxtable\Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @return	Huxtable\Bot\Twitter\Twitter
	 */
	public function getTwitterObject()
	{
		$config = $this->getConfigObject();

		if( !($this->twitter instanceof Twitter) )
		{
			$credentials = $config->getDomain( 'twitter' );
			if( is_null( $credentials ) )
			{
				throw new \Exception( "Domain 'twitter' not found in configuration data" );
			}
			$this->twitter = new Twitter( $credentials );
		}

		return $this->twitter;
	}

	/**
	 * @param	Huxtable\Bot\Twitter\Tweet	$tweet
	 * @return	void
	 */
	public function postTweetToTwitter( Tweet $tweet )
	{
		$twitter = $this->getTwitterObject();
		$response = $twitter->postTweet( $tweet );

		if( $response['httpCode'] != 200 )
		{
			$responseError = $response['body']['errors'][0];
			throw new \Exception( $responseError->message, $responseError->code );
		}
	}
}
