<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Twitter;

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
		$requiredKeys = ['consumerKey','consumerSecret','accessToken','accessTokenSecret'];
		foreach( $requiredKeys as $requiredKey )
		{
			if( !isset( $credentials[$requiredKey] ) )
			{
				throw new \InvalidArgumentException( "Missing required Twitter credentials key '{$requiredKey}'." );
			}
		}

		extract( $credentials );
		$this->connection = new TwitterOAuth( $consumerKey, $consumerSecret, $accessToken, $accessTokenSecret );
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

	/**
	 * @param	Huxtable\Bot\Twitter\Tweet	$tweet
	 * @return	array
	 */
	public function postTweet( Tweet $tweet )
	{
		$contents['status'] = $tweet->getStatus();
		$attachments = $tweet->getAttachments();

		/*
		 * Process any media attachments
		 */
		if( count( $attachments ) > 0 )
		{
			foreach( $attachments as $attachment )
			{
				$uploadResponse = $this->connection->upload( 'media/upload', ['media' => $attachment['source']] );
				$mediaIDs[] = $uploadResponse->media_id;

				if( isset( $attachment['altText'] ) )
				{
					// @todo	Add alt text when https://github.com/abraham/twitteroauth/issues/456 is fixed
				}
			}

			$contents['media_ids'] = implode( ',', $mediaIDs );
		}

		$this->connection->post( 'statuses/update', $contents );

		$response['body'] = get_object_vars( $this->connection->getLastBody() );
		$response['httpCode'] = $this->connection->getLastHttpCode();

		return $response;
	}
}
