<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Source;

use Huxtable\Core\File;
use Huxtable\Core\Utils;

class Source implements \JsonSerializable
{
	/**
	 * @var	string
	 */
	protected $author;

	/**
	 * @var	string
	 */
	protected $contents;

	/**
	 * @var	boolean
	 */
	protected $enabled;

	/**
	 * @var	int
	 */
	protected $id;

	/**
	 * @var	int
	 */
	protected $lineEnd;

	/**
	 * @var	int
	 */
	protected $lineStart;

	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $source;

	/**
	 * @var	string
	 */
	protected $title;

	/**
	 * @param	int							$id
	 * @param	string						$title
	 * @param	string						$author
	 * @param	Huxtable\Core\File\File		$source
	 * @param	boolean						$enabled
	 * @param	array						$exclusions
	 * @return	void
	 */
	public function __construct( array $metadata, File\File $source, $enabled=true, array $exclusions )
	{
		$this->id = $metadata['id'];
		$this->title = $metadata['title'];
		$this->author = $metadata['author'];

		$this->lineStart = isset( $metadata['lineStart'] ) ? $metadata['lineStart'] : 0;
		$this->lineEnd = isset( $metadata['lineEnd'] ) ? $metadata['lineEnd'] : null;

		$this->enabled = $enabled;
		$this->source = $source;

		$this->exclusions = $exclusions;
	}

	/**
	 * @return	void
	 */
	public function disable()
	{
		$this->enabled = false;
	}

	/**
	 * @return	void
	 */
	public function enable()
	{
		$this->enabled = true;
	}

	/**
	 * @return	string
	 */
	public function getAuthor()
	{
		return $this->author;
	}

	/**
	 * @return	string
	 */
	public function getContents()
	{
		if( is_null( $this->contents ) )
		{
			$contentsRaw = $this->source->getContents();
			$contentsLines = explode( PHP_EOL, $contentsRaw );

			if( is_null( $this->lineEnd ) )
			{
				$this->lineEnd = count( $contentsLines ) - 1;
			}

			$contentsSlice = array_slice( $contentsLines, $this->lineStart, ($this->lineEnd - $this->lineStart) );
			$contents = implode( PHP_EOL, $contentsSlice );
			$contents = trim( $contents );

			$this->contents = $contents;
		}

		return $this->contents;
	}

	/**
	 * @return	int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param	int		$paragraphCount
	 * @return	array
	 */
	public function getParagraphs( $paragraphCount )
	{
		$paragraphs = [];
		$lines = explode( "\n", $this->getContents() );

		/* Move "cursor" to beginning of new paragraph */
		$minLine = $this->lineStart;
		$maxLine = floor( ($this->lineEnd - $this->lineStart) * 0.75 );
		$offsetLine = rand( $minLine, $maxLine );

		do
		{
			$offsetLine++;
			$line = $lines[$offsetLine];
		}
		while( strlen( $line ) > 0 && $line != "\r" && $line != "\n" );

		/* Capture paragraphs */
		$paragraphs = [];

		while( count( $paragraphs ) < $paragraphCount )
		{
			$paragraph = [];
			$skipParagraph = false;

			do
			{
				$offsetLine++;

				$formattedLine = trim( $line );
				$formattedLine = str_replace( '_', '', $formattedLine );
				$formattedLine = str_replace( '  ', ' ', $formattedLine );

				foreach( $this->exclusions as $excludedWord )
				{
					if( substr_count( strtolower( $formattedLine ), " {$excludedWord} " ) > 0 )
					{
						$skipParagraph = true;
					}
				}

				$paragraph[] = $formattedLine;

				$line = $lines[$offsetLine];
			}
			while( strlen( $line ) > 0 && $line != "\r" && $line != "\n" );

			// Don't allow empty paragraphs
			if( count( $paragraph ) == 1 )
			{
				if( strlen( $paragraph[0] ) == 0)
				{
					continue;
				}
			}

			if( !$skipParagraph )
			{
				$paragraphs[] = implode( ' ', $paragraph );
			}

			/* Safety hatch, in case we run out of content */
			if( $offsetLine >= count( $lines ) )
			{
				break;
			}
		}

		return $paragraphs;
	}

	/**
	 * @return	Huxtable\Core\File\File
	 */
	public function getSourceFile()
	{
		return $this->source;
	}

	/**
	 * @return	string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param
	 * @return	void
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		$data['id'] = $this->id;
		$data['title'] = $this->title;
		$data['author'] = $this->author;
		$data['enabled'] = $this->enabled;
		$data['lineStart'] = $this->lineStart;

		if( !is_null( $this->lineEnd ) )
		{
			$data['lineEnd'] = $this->lineEnd;
		}

		return $data;
	}
}
