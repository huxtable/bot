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
	 * @var	Huxtable\Core\Config
	 */
	protected $config;

	/**
	 * @var	Huxtable\Bot\Corpora
	 */
	protected $corpora;

	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $dirData;

	/**
	 * @var	Huxtable\Core\File\Directory
	 */
	protected $dirTemp;

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
	 * @var	Huxtable\Bot\Twitter\Twitter
	 */
	protected $twitter;

	/**
	 * @param	string							$name			Bot name
	 * @param	Huxtable\Core\File\Directory	$dirData		ex., /var/opt/<bot>
	 * @return	void
	 */
	public function __construct( $name, File\Directory $dirData )
	{
		$this->name = $name;
		$this->dirData = $dirData;

		/* Output */
		$this->output = new Output( false );

		/* Temp Directory */
		$this->dirTemp = $dirData->childDir( 'tmp' );

		if( !$this->dirTemp->exists() )
		{
			$this->dirTemp->create();
		}

		/* History */
		$fileHistory = $this->dirData->child( 'history.json' );
		$this->history = new History( $fileHistory );

		/* Config */
		$fileConfig = $this->dirData->child( 'config.json' );
		$this->config = new Config( $fileConfig );
	}

	/**
	 * @return	Huxtable\Core\Config
	 */
	public function getConfigObject()
	{
		return $this->config;
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
		$this->flickr = new Flickr\Flickr( $flickrToken, $this->history, $this->output );

		return $this->flickr;
	}

	/**
	 * @return	Huxtable\Bot\History
	 */
	public function getHistoryObject()
	{
		return $this->history;
	}

	/**
	 * @return	Huxtable\Core\File\Directory
	 */
	public function getTempDirectory()
	{
		return $this->dirTemp;
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

	/**
	 * @param	Huxtable\Bot\Twitter\Tweet	$tweet
	 * @return	void
	 */
	public function postTweetToTwitter( Twitter\Tweet $tweet )
	{
		$twitter = $this->getTwitterObject();
		$response = $twitter->postTweet( $tweet );

		if( $response['httpCode'] != 200 )
		{
			$responseError = $response['body']['errors'][0];
			throw new \Exception( $responseError->message, $responseError->code );
		}
	}

	/**
	 * @param
	 * @return	void
	 */
	public function registerCorpora( Corpora $corpora )
	{
		$this->corpora = $corpora;
	}

	/**
	 * @return	void
	 */
	public function writeHistory()
	{
		$this->history->write();
	}
}
