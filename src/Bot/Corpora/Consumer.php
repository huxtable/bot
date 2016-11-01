<?php

/*
 * This file is part of Huxtable\Bot
 */
namespace Huxtable\Bot\Corpora;

trait Consumer
{
	/**
	 * @var	Huxtable\Bot\Corpora
	 */
	protected $corpora;

	/**
	 * @return	Huxtable\Bot\Corpora\Corpora
	 */
	public function getCorporaObject()
	{
		return $this->corpora;
	}

	/**
	 * @param
	 * @return	void
	 */
	public function registerCorpora( Corpora $corpora )
	{
		$this->corpora = $corpora;
	}
}
