<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Flickr;

use Huxtable\Bot;
use Huxtable\Core\HTTP;

class Flickr
{
	/**
	 * @var	string
	 */
	protected $apiKey;

	/**
	 * @var	Huxtable\Bot\History
	 */
	protected $history;

	/**
	 * @var	Huxtable\Bot\Output
	 */
	protected $output;

	/**
	 * @var	Huxtable\Core\HTTP\Request
	 */
	protected $request;

	/**
	 * @param	string					$apiKey
	 * @param	Huxtable\Bot\History	$history
	 * @param	Huxtable\Bot\Output		$history
	 * @return	void
	 */
	public function __construct( $apiKey, Bot\History $history, Bot\Output $output )
	{
		$this->apiKey = $apiKey;
		$this->history = $history;
		$this->output = $output;
	}

	/**
	 * @param	array	$includeTags
	 * @param	array	$excludeTags
	 * @param	boolean	$muteOwner
	 * @return	Bot\Flickr\Photo
	 */
	public function getSinglePhotoByTags( array $includeTags, array $excludeTags=[], $muteOwner=true )
	{
		// Flickr seems to have a cap on the number of tags you can use, so we might have to do some manual filtering as well
		$maxTags = 20;
		$excludeTagsQuery = array_slice( $excludeTags, 0, ($maxTags - count( $includeTags )) );
		$excludeTagsManual = array_slice( $excludeTags, ($maxTags - count( $includeTags )) );

		$searchPage = 1;
		$searchResults = $this->searchPhotosByTags( $includeTags, $excludeTagsQuery, $searchPage );

		$this->output->log( "Flickr: Search returned {$searchResults->getTotal()} results" );

		$request = $this->getFlickrRequest();

		do
		{
			try
			{
				$photo = $searchResults->getNextPhoto( $request );
			}
			catch( \UnderflowException $e )
			{
				$searchPage++;

				if( $searchPage > $searchResults->getPages() )
				{
					throw new \UnderflowException( 'Search results are exhausted' );
				}

				$searchResults = $this->searchPhotosByTags( $includeTags, $excludeTagsQuery, $searchPage );
			}

			$skip = false;

			/*
			 * Skip skipped photos
			 */
			if( $this->history->domainEntryExists( 'photo_skipped', $photo->getId() ) )
			{
				$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
				$this->output->log( 'Flickr: Photo was previously skipped' );
				continue;
			}

			/*
			 * Skip photos that are already in History
			 */
			if( $this->history->domainEntryExists( 'photo_id', $photo->getId() ) )
			{
				$this->output->log( 'Skipping: Photo already in history' );
				continue;
			}

			/*
			 * Skip muted owners
			 */
			if( $this->history->domainEntryExists( 'owner_id', $photo->getOwnerId() ) )
			{
				$this->output->log( 'Skipping: Owner is muted' );
				continue;
			}

			/*
			 * Skip photos Flickr thinks has people in them
			 */
			if( $photo->hasPeople() )
			{
				$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
				$this->output->log( 'Skipping: Photo has people' );
				continue;
			}

			/*
			 * Skip non-landscape photos
			 */
			if( !$photo->isLandscape() )
			{
				$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
				$this->output->log( 'Skipping: Photo is portrait' );
				continue;
			}

			/*
			 * Skip panorama photos
			 */
			if( $photo->isPanorama() )
			{
				$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
				$this->output->log( 'Skipping: Photo is panorama' );
				continue;
			}

			$photoTags = $photo->getTags();

			/*
			 * Skip photos with too many tags
			 */
			if( ($tagCount = count( $photoTags )) > 63 )
			{
				$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
				$this->output->log( "Skipping: Too many tags ($tagCount)" );
				continue;
			}
			$this->output->log( "Flickr: {$tagCount} tags" );

			/*
			 * Skip photos with any tags found in our batch of remainder exclude tags
			 */
			foreach( $excludeTagsManual as $excludeTag )
			{
				if( in_array( $excludeTag, $photoTags ) )
				{
					$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
					$this->output->log( "Skipping: Found excluded tag '{$excludeTag}'" );
					continue 2;
				}
			}

			/*
			 * Skip photos with no favorites
			 */
			if( ($favoritesCount = $photo->getFavorites()) < 1 )
			{
				$this->history->addDomainEntry( 'photo_skipped', $photo->getId() );
				$this->output->log( "Skipping: Not enough favorites...? ($favoritesCount)" );
				continue;
			}

			break;
		}
		while( true );

		// Update History
		$this->history->addDomainEntry( 'photo_id', $photo->getId() );
		if( $muteOwner )
		{
			$this->history->addDomainEntry( 'owner_id',	$photo->getOwnerId() );
		}
		$this->history->write();
		return $photo;
	}

	/**
	 * @param	array	$includeTags
	 * @param	array	$excludeTags
	 * @param	int		$page
	 * @return	Bot\Flickr\SearchResults
	 */
	protected function searchPhotosByTags( array $includeTags, array $excludeTags=[], $page )
	{
		$http = new HTTP;
		$request = $this->getFlickrRequest();

		// Build tag query
		$includedTagsString	= implode( ',', $includeTags );
		$excludedTagsString = empty( $excludeTags ) ? '' : ',-' . implode( ',-', $excludeTags );
		$tagsString = "{$includedTagsString}{$excludedTagsString}";

		$request->addParameter( 'license', 1 );				// Creative Commons: Attribution-NonCommercial-ShareAlike License
		$request->addParameter( 'content_type', 1 );		// Photos only
		$request->addParameter( 'media', 'photos' );
		$request->addParameter( 'method', 'flickr.photos.search' );
		$request->addParameter( 'page', $page );
		$request->addParameter( 'per_page', 200 );
		$request->addParameter( 'safe_search', 1 );
		$request->addParameter( 'sort', 'interestingness-desc' );
		$request->addParameter( 'tags', $tagsString );
		$request->addParameter( 'tag_mode', 'all' );		// Uses 'AND' combination
		$request->addParameter( 'extras', 'machine_tags' );

		$this->output->log( "Flickr: Searching for '{$includedTagsString}', page {$page}" );

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );
		$searchResults = new SearchResults( $httpResponseBody, $this->output );

		return $searchResults;
	}

	/**
	 * @return	Huxtable\Core\HTTP\Request
	 */
	protected function getFlickrRequest()
	{
		$request = new HTTP\Request( "https://api.flickr.com/services/rest" );

		$request->addParameter( 'api_key', $this->apiKey );
		$request->addParameter( 'format', 'php_serial' );

		return $request;
	}
}
