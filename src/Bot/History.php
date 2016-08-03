<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Huxtable\Core\File;

class History
{
	/**
	 * @var	array
	 */
	public $items = [];

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\File\File		$file
	 * @return	void
	 */
	public function __construct( File\File $source )
	{
		$this->source = $source;
		if( $source->exists() )
		{
			$json = $source->getContents();
			$data = json_decode( $json, true );

			if( isset( $data['items'] ) )
			{
				$this->items = $data['items'];
			}
		}
	}

	/**
	* @param	string	$domain
	* @param	string	$value
	* @return	void
	*/
	public function addDomainEntry( $domain, $value )
	{
		$this->items[$domain][] = $value;
		$this->write();
	}

	/**
	 * @param	string	$domain
	 * @param	string	$value
	 * @return	boolean
	 */
	public function domainEntryExists( $domain, $value )
	{
		if( !isset( $this->items[$domain] ) )
		{
			return false;
		}

		return in_array( $value, $this->items[$domain] );
	}

	/**
	 * @param	string	$domain
	 * @return	void
	 */
	public function resetDomain( $domain )
	{
		if( isset( $this->items[$domain] ) )
		{
			$this->items[$domain] = [];
			$this->write();
		}
	}

	/**
	 * @return	void
	 */
	public function write()
	{
		$data = json_encode( $this, JSON_PRETTY_PRINT );
		$this->source->putContents( $data );
	}
}
