<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

class Output
{
	/**
	 * @var	boolean
	 */
	protected $echo;

	/**
	 * @param	boolean		$echo
	 * @return	void
	 */
	public function __construct( $echo )
	{
		$this->echo = $echo;
	}

	/**
	 * @param	string	$string
	 * @return	void
	 */
	public function line( $string )
	{
		echo $string . PHP_EOL;
	}

	/**
	 * @param	string	$string
	 * @return	void
	 */
	public function log( $string )
	{
		if( $this->echo )
		{
			echo "> {$string}" . PHP_EOL;
		}
	}

}
