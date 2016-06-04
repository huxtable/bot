<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Flickr;

use Huxtable\Bot;
use Huxtable\Core\File;
use Huxtable\Core\HTTP;

class Photo
{
	/**
	 * @var	array
	 */
	protected $exif;

	/**
	 * @var	boolean
	 */
	protected $hasPeople;

	/**
	 * @var	int
	 */
	protected $height;

	/**
	 * @var	string
	 */
	protected $id;

	/**
	 * @var	Huxtable\Bot\Output
	 */
	protected $output;

	/**
	 * @var	string
	 */
	protected $ownerId;

	/**
	 * @var	int
	 */
	protected $ratio;

	/**
	 * @var	Huxtable\Core\HTTP\Request
	 */
	protected $request;

	/**
	 * @var	string
	 */
	protected $secret;

	/**
	 * @var	string
	 */
	protected $source;

	/**
	 * @var	array
	 */
	protected $tags=[];

	/**
	 * @var	string
	 */
	protected $url;

	/**
	 * @var	int
	 */
	protected $width;

	/**
	 * @param	array						$photoInfo
	 * @param	Huxtable\Core\HTTP\Request	$request
	 * @param	Huxtable\Bot\Output			$output
	 * @return	void
	 */
	public function __construct( array $photoInfo, HTTP\Request $request, Bot\Output $output )
	{
		$this->id = $photoInfo['id'];
		$this->ownerId = $photoInfo['owner'];
		$this->secret = $photoInfo['secret'];
		$this->machineTags = $photoInfo['machine_tags'];

		$this->request = $request;
		$this->output = $output;
	}

	/**
	 * @param	Huxtable\Core\File	$file
	 * @return	Huxtable\Bot\Image
	 */
	public function downloadToFile( File\File $file )
	{
		$http = new HTTP();
		$request = new HTTP\Request( $this->source );

		$this->output->log( 'Flickr: Downloading photo...' );
		$httpResponse = $http->get( $request );
		$file->putContents( $httpResponse->getBody() );
		$this->output->log( 'Flickr: ...done.' );

		return new Bot\Image( $file, $this->output );
	}

	/**
	 * @return	string
	 */
	public function getCamera()
	{
		if( is_null( $this->exif ) )
		{
			$this->getExifData();
		}

		return $this->exif['camera'];
	}

	/**
	 * @return	void
	 */
	protected function getExifData()
	{
		$http = new HTTP;
		$request = $this->getRequest();

		$request->addParameter( 'method',	'flickr.photos.getExif' );
		$request->addParameter( 'photo_id',	$this->id );

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );

		// Default values
		$this->exif['camera'] = null;
		$this->exif['tags'] = [];

		if( isset( $httpResponseBody['photo'] ) )
		{
			$this->exif['camera'] = $httpResponseBody['photo']['camera'];
			$this->exif['tags'] = $httpResponseBody['photo']['exif'];
		}
	}

	/**
	 * @return	array
	 */
	public function getExifTags()
	{
		if( is_null( $this->exif ) )
		{
			$this->getExifData();
		}

		return $this->exif['exif'];
	}

	/**
	 * @return	int
	 */
	public function getFavorites()
	{
		$http = new HTTP;
		$request = $this->getRequest();

		$request->addParameter( 'method',	'flickr.photos.getFavorites' );
		$request->addParameter( 'photo_id',	$this->id );
		$request->addParameter( 'page',		1 );
		$request->addParameter( 'per_page',	1 );

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );

		$photoInfo = $httpResponseBody['photo'];
		return $photoInfo['total'];
	}

	/**
	 * @return	int
	 */
	public function getHeight()
	{
		if( is_null( $this->height ) )
		{
			$this->getSizes();
		}

		return $this->height;
	}

	/**
	 * @return	string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return	void
	 */
	protected function getInfo()
	{
		$http = new HTTP;
		$request = $this->getRequest();

		$request->addParameter( 'method',	'flickr.photos.getInfo' );
		$request->addParameter( 'photo_id',	$this->id );
		$request->addParameter( 'secret',	$this->secret );

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );

		$photoInfo = $httpResponseBody['photo'];

		// Capture tag strings
		$tagsInfo = $photoInfo['tags']['tag'];
		foreach( $tagsInfo as $tagInfo )
		{
			$this->tags[] = mb_strtolower( $tagInfo['raw'] );
		}

		// URL to tweet
		$this->url = $photoInfo['urls']['url'][0]['_content'];

		// Experimental
		$this->hasPeople = $photoInfo['people']['haspeople'] === 1;
	}

	/**
	 * @return	string
	 */
	public function getMachineTags()
	{
		return $this->machineTags;
	}

	/**
	 * @return	string
	 */
	public function getOwnerId()
	{
		return $this->ownerId;
	}

	/**
	 * @return	Huxtable\Core\HTTP\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @return	string
	 */
	public function getSecret()
	{
		return $this->secret;
	}

	/**
	 * @return	void
	 */
	protected function getSizes()
	{
		$http = new HTTP;
		$request = $this->getRequest();

		$request->addParameter( 'method',	'flickr.photos.getSizes' );
		$request->addParameter( 'photo_id',	$this->id );

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );

		$sizeInfo = $httpResponseBody['sizes']['size'];

		// Find the version whose width is closest to 640
		foreach( $sizeInfo as $sizeIndex => $info )
		{
			if( $info['width'] >= 640 )
			{
				break;
			}
		}

		$this->height	= $sizeInfo[$sizeIndex]['height'];
		$this->width	= $sizeInfo[$sizeIndex]['width'];
		$this->ratio	= $this->height / $this->width;

		$this->source	= $sizeInfo[$sizeIndex]['source'];
	}

	/**
	 * @return	string
	 */
	public function getSource()
	{
		return $this->source;
	}

	/**
	 * @return	int
	 */
	public function getFavorites()
	{
		$http = new HTTP;
		$request = $this->getRequest();

		$request->addParameter( 'method',	'flickr.photos.getFavorites' );
		$request->addParameter( 'photo_id',	$this->id );
		$request->addParameter( 'page',		1 );
		$request->addParameter( 'per_page',	1 );

		$httpResponse = $http->get( $request );
		$httpResponseBody = unserialize( $httpResponse->getBody() );

		$photoInfo = $httpResponseBody['photo'];
		return $photoInfo['total'];
	}

	/**
	 * @return	array
	 */
	public function getTags()
	{
		if( empty( $this->tags ) )
		{
			$this->getInfo();
		}

		return $this->tags;
	}

	/**
	 * @return	string
	 */
	public function getURL()
	{
		if( is_null( $this->url ) )
		{
			$this->getInfo();
		}

		return $this->url;
	}

	/**
	 * @return	boolean
	 */
	public function hasPeople()
	{
		if( is_null( $this->hasPeople ) )
		{
			$this->getInfo();
		}

		return $this->hasPeople;
	}

	/**
	 * @return	boolean
	 */
	public function isLandscape()
	{
		if( is_null( $this->ratio ) )
		{
			$this->getSizes();
		}

		return $this->ratio <= 0.9;
	}

	/**
	 * @return	boolean
	 */
	public function isPanorama()
	{
		if( is_null( $this->ratio ) )
		{
			$this->getSizes();
		}

		return $this->ratio <= 0.5;
	}
}
