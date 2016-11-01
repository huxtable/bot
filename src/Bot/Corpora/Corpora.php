<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Corpora;

use Huxtable\Bot\History;
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
	 * @param	Huxtable\Bot\History\History	$history
	 * @return	void
	 */
	public function __construct( File\Directory $source, History\History $history=null )
	{
		$this->source = $source;
		$this->history = $history;
	}

	/**
	 * @param	string	$categoryName
	 * @param	string	$corpusName
	 * @param	string	$selector
	 * @return	mixed
	 */
	public function getItem( $categoryName, $corpusName, $selector = null )
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

		$historyDomain = "corpus_{$categoryName}_{$corpusName}";

		$corpusData = json_decode( $corpusFile->getContents(), true );
		$dataSelector = is_null( $selector ) ? $corpusName : $selector;

		$corpusItems = $corpusData[$dataSelector];

		if( !is_null( $this->history ) )
		{
			$corpusItemsUnused = [];

			foreach( $corpusItems as $corpusItem )
			{
				if( !$this->history->domainEntryExists( $historyDomain, $corpusItem ) )
				{
					$corpusItemsUnused[] = $corpusItem;
				}
			}

			if( count( $corpusItemsUnused ) == 0 )
			{
				$this->history->resetDomain( $historyDomain );
				$corpusItemsUnused = $corpusItems;
			}

			$corpusItem = Utils::randomElement( $corpusItemsUnused );
			$this->history->addDomainEntry( $historyDomain, $corpusItem );
		}
		else
		{
			$corpusItem = Utils::randomElement( $corpusItems );
		}

		return $corpusItem;
	}
}
