<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Huxtable\Core\File;

class Image
{
	/**
	 * @var	Huxtable\Core\File\File
	 */
	protected $source;

	/**
	 * @param	Huxtable\Core\File\File		$source
	 * @param	Huxtable\Bot\Output			$output
	 * @return	void
	 */
	public function __construct( File\File $source, Output $output )
	{
		$this->source = $source;
		$this->output = $output;
	}

	/**
	 * @param	string	$text
	 * @param	int		$fontSize
	 * @param	string	$fontColor
	 * @return	void
	 */
	public function addCaption( $text, $fontSize, $fontPath, $fontColor, $height, $shadowOffset=3 )
	{
		$this->output->log( "Image: Adding caption '{$text}'" );

		$dirTemp = $this->source->parent();

		$fileBlack = $dirTemp->child( 'text-black.png' );
		$fileColor = $dirTemp->child( 'text-color.png' );
		$fileShadow = $dirTemp->child( 'text-shadow.png' );

		$gravity = 'center';
		$heightShadow = $height - $shadowOffset;

		exec( "convert -background \"#ffffff\" -fill \"#000000\" -font {$fontPath} -pointsize {$fontSize} -size 640x{$heightShadow} -gravity {$gravity} label:\"{$text}\" {$fileShadow}" );
		exec( "convert -background \"#ffffff\" -fill \"#000000\" -font {$fontPath} -pointsize {$fontSize} -size 640x{$height} -gravity {$gravity} label:\"{$text}\" {$fileBlack}" );
		exec( "convert -background \"#000000\" -fill \"#{$fontColor}\" -font {$fontPath} -pointsize {$fontSize} -size 640x{$height} -gravity {$gravity} label:\"{$text}\" {$fileColor}" );

		exec( "composite -gravity south -compose multiply {$fileShadow} {$this->source} {$this->source}" );
		exec( "composite -gravity south -compose multiply {$fileBlack} {$this->source} {$this->source}" );
		exec( "composite -gravity south -compose screen {$fileColor} {$this->source} {$this->source}" );
	}

	/**
	 * @param	int		$height
	 * @param	int		$width
	 * @return	void
	 */
	public function resize( $height, $width )
	{
		$this->output->log( "Image: Resizing to {$height}x{$width}" );
		exec( "convert {$this->source} -resize '{$height}x{$width}' {$this->source}" );
	}
}
