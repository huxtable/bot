<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Source;

use Huxtable\Core\File;
use Huxtable\Core\HTTP;
use Huxtable\Core\Utils;

trait Consumer
{
	/**
	 * @var	array
	 */
	protected $exclusions = [];

	/**
	 * @var	array
	 */
	protected $sources;

	/**
	 * @param	int		$id
	 */
	public function addSourceById( $id )
	{
		/*
		 * Look for existing source
		 */
		$sources = $this->getSources();
		foreach( $sources as $source )
		{
			if( $source->getId() == $id )
			{
				return $source;
			}
		}

		/*
		 * Build URL
		 *
		 * ex., for id 1234 — http://gutenberg.readingroo.ms/1/2/3/1234/1234-8.txt
		 */
		$urlBase = 'http://gutenberg.readingroo.ms';

		$digits = str_split( $id );
		array_pop( $digits );

		$url = sprintf( '%s/%s/%s/%s.txt', $urlBase, implode( '/', $digits ), $id, $id );

		/*
		 * Download to temporary storage
		 */
		$dirTemp = $this->getTempDirectory();
		$fileTemp = $dirTemp->child( "{$id}.txt" );

		$request = new HTTP\Request( $url );
		$response = HTTP::get( $request );
		$sourceContents = $response->getBody();

		$fileTemp->putContents( $sourceContents );

		/*
		 * Parse source and save contents to disk
		 */
		$dirSource = $this->getSourcesDirectory();
		$fileSource = $dirSource->child( "{$id}.txt" );

		$sourceData = $this->parseRawSource( $fileTemp );
		$fileSource->putContents( $sourceData['contents'] );

		$source = new Source( $sourceData, $fileSource, true, $this->exclusions );

		/*
		 * Update sources.json
		 */
		$this->sources[] = $source;
		$this->writeSources();

		/*
		 * Cleanup
		 */
		$fileTemp->delete();

		return $source;
	}

	/**
	 * @param	string	$id
	 * @return	void
	 */
	public function deleteSourceById( $id )
	{
		$sources = $this->getSources();
		for( $s = 0; $s < count( $sources ); $s++ )
		{
			$source = $sources[$s];
			if( $source->getId() == $id )
			{
				unset( $this->sources[$s] );
				Utils::reindexArray( $this->sources );

				$sourceFile = $source->getSourceFile();
				$sourceFile->delete();
			}
		}

		$this->writeSources();
	}

	/**
	 * @return	void
	 */
	public function disableAllSources()
	{
		$sources = $this->getSources();
		foreach( $sources as &$source )
		{
			$source->disable();
		}

		$this->writeSources();
	}

	/**
	 * @param	string	$id
	 * @return	void
	 */
	public function disableSourceById( $id )
	{
		$source = $this->getSourceById( $id );
		$source->disable();
		$this->writeSources();
	}

	/**
	 * @return	void
	 */
	public function enableAllSources()
	{
		$sources = $this->getSources();
		foreach( $sources as &$source )
		{
			$source->enable();
		}

		$this->writeSources();
	}

	/**
	 * @param	string	$id
	 * @return	void
	 */
	public function enableSourceById( $id )
	{
		$source = $this->getSourceById( $id );
		$source->enable();
		$this->writeSources();
	}

	/**
	 * @return	array
	 */
	public function getEnabledSources()
	{
		$sources = $this->getSources();
		$enabledSources = [];

		foreach( $sources as $source )
		{
			if( $source->isEnabled() )
			{
				$enabledSources[] = $source;
			}
		}

		return $enabledSources;
	}

	/**
	 * @return	HilariousFixes\Source
	 */
	public function getRandomSource()
	{
		$historyDomain = 'source_ids';
		$history = $this->getHistoryObject();

		$sources = $this->getEnabledSources();
		$sourcesUnused = [];

		foreach( $sources as $source )
		{
			if( !$history->domainEntryExists( $historyDomain, $source->getId() ) )
			{
				$sourcesUnused[] = $source;
			}
		}

		if( count( $sourcesUnused ) == 0 )
		{
			$history->resetDomain( $historyDomain );
			$sourcesUnused = $sources;
		}

		$source = Utils::randomElement( $sourcesUnused );
		$history->addDomainEntry( $historyDomain, $source->getId() );

		return $source;
	}

	/**
	 * @param	string	$id
	 * @return	MLIB\Source
	 */
	public function getSourceById( $id )
	{
		$sources = $this->getSources();
		foreach( $sources as &$source )
		{
			if( $source->getId() == $id )
			{
				return $source;
			}
		}

		throw new \Exception( "Unknown source id '{$id}'" );
	}

	/**
	 * Return array of Source objects
	 *
	 * @return	array
	 */
	public function getSources()
	{
		if( is_array( $this->sources ) )
		{
			return $this->sources;
		}

		$sources = [];

		/*
		 * Read from sources.json
		 */
		$fileSources = $this->dirData->child( 'sources.json' );
		if( !$fileSources->exists() )
		{
			return $sources;
		}

		$dirSources = $this->getSourcesDirectory();

		$jsonSources = $fileSources->getContents();
		$dataSources = json_decode( $jsonSources, true );

		foreach( $dataSources as $sourceData )
		{
			$fileSource = $dirSources->child( "{$sourceData['id']}.txt" );

			if( !$fileSource->exists() )
			{
				throw new \Exception( "Unknown source id '{$id}'" );
			}

			$source = new Source( $sourceData, $fileSource, $sourceData['enabled'], $this->exclusions );
			$sources[] = $source;
		}

		$this->sources = $sources;

		return $this->sources;
	}

	/**
	 * @return	Huxtable\Core\File\Directory
	 */
	public function getSourcesDirectory()
	{
		$dirSources = $this->dirData->childDir( 'sources' );

		if( !$dirSources->exists() )
		{
			$dirSources->create();
		}

		return $dirSources;
	}

	/**
	 * @param	Huxtable\Core\File\File	$fileSource
	 * @return	array
	 */
	public function parseRawSource( File\File $fileSource )
	{
		$contentsRaw = $fileSource->getContents();

		/*
		 * ID
		 */
		$source['id'] = $fileSource->getBasename( '.txt' );

		/*
		 * Title
		 */
		$patternTitle = '/^Title: (.*)?$/mi';
		preg_match( $patternTitle, $contentsRaw, $matchesTitle );

		if( isset( $matchesTitle[1] ) )
		{
			$source['title'] = trim( $matchesTitle[1] );
		}
		else
		{
			$source['title'] = 'Unknown';
		}

		/*
		 * Author
		 */
		$patternAuthor = '/^Author: (.*)?$/mi';
		preg_match( $patternAuthor, $contentsRaw, $matchesAuthor );

		if( isset( $matchesTitle[1] ) )
		{
			$source['author'] = trim( $matchesAuthor[1] );
		}
		else
		{
			$source['author'] = 'Unknown';
		}

		/*
		 * Contents
		 */
		$source['contents'] = $fileSource->getContents();

		$contentsLines = explode( PHP_EOL, $contentsRaw );

		/* Look for offset start */
		$currentLine = 0;
		$patternStart = '/^\*\*\*\s?START OF/';
		foreach( $contentsLines as $line )
		{
			preg_match( $patternStart, $line, $matches );
			if( count( $matches ) > 0 )
			{
				$source['lineStart'] = $currentLine + 1;
				break;
			}

			$currentLine++;
		}

		/* Look for offset end */
		$currentLine = 0;
		$patternEnd = '/^\*\*\*\s?END OF/';
		foreach( $contentsLines as $line )
		{
			preg_match( $patternEnd, $line, $matches );
			if( count( $matches ) > 0 )
			{
				$source['lineEnd'] = $currentLine - 1;
				break;
			}

			$currentLine++;
		}

		return $source;
	}

	/**
	 * @return	void
	 */
	public function writeSources()
	{
		$jsonSources = json_encode( $this->sources, JSON_PRETTY_PRINT );
		$fileSources = $this->dirData->child( 'sources.json' );

		$fileSources->putContents( $jsonSources );
	}
}
