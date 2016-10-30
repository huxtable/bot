<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\History;

use Huxtable\Core\File;

trait Consumer
{
	/**
	 * @var	Huxtable\Bot\History\History
	 */
	protected $history;

	/**
	 * @return	Huxtable\Bot\History
	 */
	public function getHistoryObject()
	{
		if( $this->history instanceof History )
		{
			return $this->history;
		}

		$fileHistory = $this->dirData->child( 'history.json' );
		$this->history = new History( $fileHistory );

		return $this->history;
	}

	/**
	 * Write history to disk
	 */
	public function writeHistory()
	{
		if( !($this->history instanceof History) )
		{
			throw new \InvalidArgumentException( 'History object is not instantiated' );
		}

		$this->history->write();
	}
}
