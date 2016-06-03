<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Flickr;

use Huxtable\Core\HTTP;

class Flickr
{
	/**
	 * @var	string
	 */
	protected $apiKey;

	/**
	 * @var	Huxtable\Core\HTTP\Request
	 */
	protected $request;

	/**
	 * @param	string	$apiKey
	 * @return	void
	 */
	public function __construct( $apiKey )
	{
		$this->apiKey = $apiKey;
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

				echo ">> Page {$searchPage}" . PHP_EOL;
				$searchResults = $this->searchPhotosByTags( $includeTags, $excludeTagsQuery, $searchPage );
			}

			$skip = false;

			/*
			 * Skip photos that are already in History
			 */
			// if( $this->history->domainEntryExists( 'photo_id', $photo->getId() ) )
			{
				// continue;
			}

			/*
			 * Skip muted owners
			 */
			// if( $this->history->domainEntryExists( 'owner_id', $photo->getOwnerId() ) )
			{
				// continue;
			}

			/*
			 * Skip photos Flickr thinks has people in them
			 */
			if( $photo->hasPeople() )
			{
				continue;
			}

			/*
			 * Skip non-landscape photos
			 */
			if( !$photo->isLandscape() )
			{
				continue;
			}

			/*
			 * Skip panorama photos
			 */
			if( $photo->isPanorama() )
			{
				continue;
			}

			$photoTags = $photo->getTags();

			/*
			 * Skip photos with too many tags`
			 */
			if( count( $photoTags ) > 35 )
			{
				continue;
			}

			/*
			 * Skip photos with any tags found in our batch of remainder exclude tags
			 */
			foreach( $excludeTagsManual as $excludeTag )
			{
				if( in_array( $excludeTag, $photoTags ) )
				{
					continue;
				}
			}
			break;
		}
		while( true );

		// Update History
		// $this->history->addDomainEntry( 'photo_id', $photo->getId() );
		if( $muteOwner )
		{
			// $this->history->addDomainEntry( 'owner_id',	$photo->getOwnerId() );
		}
		// $this->history->write();
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

		$request->addParameter( 'license', 1 );			// Creative Commons: Attribution-NonCommercial-ShareAlike License
		$request->addParameter( 'media', 'photos' );
		$request->addParameter( 'method', 'flickr.photos.search' );
		$request->addParameter( 'page', $page );
		$request->addParameter( 'per_page', 200 );
		$request->addParameter( 'safe_search', 1 );
		$request->addParameter( 'sort', 'interestingness-desc' );
		$request->addParameter( 'tags', $tagsString );
		$request->addParameter( 'tag_mode', 'all' );	// Uses 'AND' combination

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );
		$searchResults = new SearchResults( $httpResponseBody );

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
