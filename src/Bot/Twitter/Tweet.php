<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Twitter;

use Huxtable\Core\File;

class Tweet
{
	const STATUS_MAX_LENGTH = 140;
	const ATTACHMENT_MAX_COUNT = 4;

	/**
	 * Array of File objects to upload with tweet
	 *
	 * @var	array
	 */
	protected $attachments=[];

	/**
	 * @var	string
	 */
	protected $status;

	/**
	 * @return	void
	 */
	public function __construct( $status = '' )
	{
		$this->setStatus( $status );
	}

	/**
	 * @param	Huxtable\Core\File\File		$attachment
	 * @return	void
	 */
	public function attachMedia( File\File $source, $altText=null )
	{
		if( count( $this->attachments ) >= self::ATTACHMENT_MAX_COUNT )
		{
			throw new \LengthException( 'Maximum number of attachments reached' );
		}
		if( !$source->exists() )
		{
			throw new \Exception( "Attachment not found '{$source}'" );
		}

		$attachment['source'] = $source;
		if( !is_null( $altText ) )
		{
			$attachment['altText'] = $altText;
		}

		$this->attachments[] = $attachment;
	}

	/**
	 * @return	array
	 */
	public function getAttachments()
	{
		return $this->attachments;
	}

	/**
	 * @return	string
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * @param	string	$status
	 * @return	void
	 */
	public function setStatus( $status )
	{
		if( mb_strlen( $status ) > self::STATUS_MAX_LENGTH )
		{
			throw new \LengthException( 'Status exceeds maximum allowed length' );
		}

		$this->status = $status;
	}
}
