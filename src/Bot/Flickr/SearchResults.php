<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Flickr;

use Huxtable\Bot;
use Huxtable\Core\HTTP;

class SearchResults
{
	/**
	 * @var	Huxtable\Bot\Output
	 */
	protected $output;

	/**
	 * Current search results page
	 *
	 * @var	int
	 */
	protected $page;

	/**
	 * Number of total pages in search results
	 *
	 * @var	int
	 */
	protected $pages;

	/**
	 * Number of photo results included per page
	 *
	 * @var	int
	 */
	protected $perpage;

	/**
	 * @var	array
	 */
	protected $photos;

	/**
	 * Number of total photo results
	 *
	 * @var	int
	 */
	protected $total;

	/**
	 * @param	array					$httpResponseBody
	 * @param	Huxtable\Bot\Output		$output
	 * @return	void
	 */
	public function __construct( array $httpResponseBody, Bot\Output $output )
	{
		$this->page		= $httpResponseBody['photos']['page'];
		$this->pages	= $httpResponseBody['photos']['pages'];
		$this->perpage	= $httpResponseBody['photos']['perpage'];
		$this->total	= $httpResponseBody['photos']['total'];
		$this->photos	= $httpResponseBody['photos']['photo'];

		$this->output = $output;
	}

	/**
	 * Use the first element of the photo results array to create a Photo object
	 *
	 * @param	Huxtable\Core\HTTP\Request	$request
	 * @return	Bot\Flickr\Photo
	 */
	public function getNextPhoto( HTTP\Request $request )
	{
		if( count( $this->photos ) > 0 )
		{
			$photoInfo = array_shift( $this->photos );
			$photo = new Photo( $photoInfo, $request, $this->output );

			return $photo;
		}

		throw new \UnderflowException( 'Result queue is empty' );
	}

	/**
	 * @return	int
	 */
	public function getPages()
	{
		return $this->pages;
	}
}
