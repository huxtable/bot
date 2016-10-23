<?php

/*
 * This file is part of Huxtable\Bot\Slack
 */
namespace Huxtable\Bot\Slack;

class Message implements \JsonSerializable
{
	/**
	 * @var	boolean
	 */
	protected $asUser;

	/**
	 * @var	array
	 */
	protected $attachments=[];

	/**
	 * @var	string
	 */
	protected $channel;

	/**
	 * @var	string
	 */
	protected $iconEmoji;

	/**
	 * @var	string
	 */
	protected $iconURL;

	/**
	 * @var	int
	 */
	protected $linkNames;

	/**
	 * @var	string
	 */
	protected $parse;

	/**
	 * @var	string
	 */
	protected $text;

	/**
	 * @var	boolean
	 */
	protected $unfurlLinks;

	/**
	 * @var	boolean
	 */
	protected $unfurlMedia;

	/**
	 * @var	string
	 */
	protected $username;

	/**
	 * @param	Slack\Request
	 * @return	Slack\Request
	 */
	public function populateRequest( Request $request )
	{
		// $request->addParameter( 'text', $this->text );
		// $request->addParameter( 'channel', $this->channel );


		$request->setPostData( json_encode( $this ) );

		return $request;
	}

	/**
	 * @param
	 * @return	void
	 */
	public function addAttachment( Attachment $attachment )
	{
		$this->attachments[] = $attachment;
	}

	/**
	 * @param	string	$emoji
	 */
	public function setEmoji( $emoji )
	{
		$this->iconEmoji = ":{$emoji}:";
	}

	/**
	 * @param	string	$username
	 */
	public function setUsername( $username )
	{
		$this->username = $username;
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		$data = [];

		if( !is_null( $this->text ) )
		{
			$data['text'] = $this->text;
		}
		if( !is_null( $this->iconEmoji ) )
		{
			$data['icon_emoji'] = $this->iconEmoji;
		}
		if( !is_null( $this->username ) )
		{
			$data['username'] = $this->username;
		}
		if( !empty( $this->attachments ) )
		{
			$data['attachments'] = $this->attachments;
		}

		return $data;
	}
}
