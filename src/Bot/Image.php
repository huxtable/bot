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
	 * @param	Huxtable\Core\File\File		$fileJPG
	 * @return	void
	 */
	public function __construct( File\File $fileJPG )
	{
		// Resize and convert the source JPG
		// $filePNG = $fileJPG->parent()->child( 'source.png' );
		exec( "convert {$fileJPG} -resize '640x640' {$fileJPG}" );

		$this->source = $fileJPG;
	}

	/**
	 * @param	string	$text
	 * @param	int		$fontSize
	 * @param	string	$fontColor
	 * @return	void
	 */
	public function addCaption( $text, $fontSize, $fontColor, $height, $shadowOffset=3 )
	{
		//function generateCaption( $text, $fontSize, $height, $shadowOffset=3, $gravity='center' )

		$dirTemp = $this->source->parent();

		$fileBlack = $dirTemp->child( 'text-black.png' );
		$fileColor = $dirTemp->child( 'text-color.png' );
		$fileShadow = $dirTemp->child( 'text-shadow.png' );

		echo $text . PHP_EOL;

		$gravity = 'center';
		$pathFont = '/Library/Fonts/CooperBlackLTPro.otf';
		$heightShadow = $height - $shadowOffset;

		exec( "convert -background \"#ffffff\" -fill \"#000000\" -font {$pathFont} -pointsize {$fontSize} -size 640x{$heightShadow} -gravity {$gravity} label:\"{$text}\" {$fileShadow}" );
		exec( "convert -background \"#ffffff\" -fill \"#000000\" -font {$pathFont} -pointsize {$fontSize} -size 640x{$height} -gravity {$gravity} label:\"{$text}\" {$fileBlack}" );
		exec( "convert -background \"#000000\" -fill \"#{$fontColor}\" -font {$pathFont} -pointsize {$fontSize} -size 640x{$height} -gravity {$gravity} label:\"{$text}\" {$fileColor}" );

		exec( "composite -gravity south -compose multiply {$fileShadow} {$this->source} {$this->source}" );
		exec( "composite -gravity south -compose multiply {$fileBlack} {$this->source} {$this->source}" );
		exec( "composite -gravity south -compose screen {$fileColor} {$this->source} {$this->source}" );
	}
}
