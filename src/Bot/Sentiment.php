<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot;

use Huxtable\Core\File;

class Sentiment
{
	/**
	 * @var	array
	 */
	protected $scores=[];

	/**
	 * @return	void
	 */
	public function __construct()
	{
		$pathData = dirname( dirname( __DIR__ ) ) . '/data/AFINN-111.txt';
		$fileData = new File\File( $pathData );

		$lines = explode( "\n", $fileData->getContents() );
		foreach( $lines as $line )
		{
			$pieces = explode( "\t", $line );

			if( count( $pieces ) == 2 )
			{
				$word = strtolower( $pieces[0] );
				$this->scores[$pieces[0]] = $pieces[1];
			}
		}
	}

	/**
	 * @param	string	$sentence
	 * @return	array
	 */
	public function getSentenceScore( $sentence )
	{
		$words = explode( ' ', $sentence );

		$score = 0;
		$scoredWords = 0;

		foreach( $words as $word )
		{
			$wordScore = $this->getWordScore( $word );
			$score = $score + $wordScore;

			if( $wordScore != 0 )
			{
				$scoredWords++;
			}
		}

		// Smoothing for divide by zero errors
		if( $scoredWords == 0 )
		{
			$scoredWords = 1;
		}

		$scores['total'] = $score;
		$scores['scoredAverage'] = $score / $scoredWords;
		$scores['totalAverage']	= $score / count( $words );

		return $scores;
	}

	/**
	 * @param	string	$word
	 * @return	int
	 */
	public function getWordScore( $word )
	{
		$wordNormalized = strtolower( $word );
		$score = isset( $this->scores[$wordNormalized] ) ? $this->scores[$wordNormalized] : 0;

		return $score;
	}
}
