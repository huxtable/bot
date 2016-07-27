<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Huxtable\Core\File;
use Huxtable\Core\Utils;

class Corpora
{
	/**
	 * @var	Huxtable\Core\File\file
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\File\Directory	$source
	 * @param	Huxtable\Bot\History			$history
	 * @return	void
	 */
	public function __construct( File\Directory $source, History $history=null )
	{
		$this->source = $source;
		$this->history = $history;
	}

	/**
	 * @param
	 * @return	void
	 */
	public function getItem( $categoryName, $corpusName, \Closure $callback = null, $selector = null )
	{
		$dirCategory = $this->source->childDir( $categoryName );

		if( is_array( $corpusName ) )
		{
			$corpusNameOptions = $corpusName;
			$corpusName = Utils::randomElement( $corpusNameOptions );
		}
		$corpusFile = $dirCategory->child( "{$corpusName}.json" );

		if( !$corpusFile->exists() )
		{
			if( !$dirCategory->exists() )
			{
				throw new \Exception( "Undefined category '{$categoryName}'" );
			}

			throw new \Exception( "Undefined corpus '{$corpusName}'" );
		}

		$corpusData = json_decode( $corpusFile->getContents(), true );
		$dataSelector = is_null( $selector ) ? $corpusName : $selector;
		$corpus = $corpusData[$dataSelector];

		$attempts = 0;
		do
		{
			$item = Utils::randomElement( $corpus );

			if( !is_null( $callback ) )
			{
				$item = call_user_func( $callback, $item );
			}

			/*
			 * Check History
			 */
			if( !is_null( $this->history ) )
			{
				$historyDomain = "corpus_{$categoryName}_{$corpusName}";

				if( $this->history->domainEntryExists( $historyDomain, $item ) )
				{
					$item = false;
				}

				/*
				 * Reset corpus
				 */
				if( $attempts >= count( $corpus ) )
				{
					$this->history->resetDomain( $historyDomain );
					$attempts = 0;
				}
				else
				{
					$attempts++;
				}
			}
		}
		while( $item === false );

		/*
		 * Add to History
		 */
		if( !is_null( $this->history ) )
		{
			$this->history->addDomainEntry( $historyDomain, $item );
		}

		return $item;
	}
}
