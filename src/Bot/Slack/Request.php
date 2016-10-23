<?php

/*
 * This file is part of Huxtable\Bot\Slack
 */
namespace Huxtable\Bot\Slack;

use Huxtable\Core\HTTP;

class Request extends HTTP\Request
{
	/**
	 * @param	string	webhookURL
	 */
	public function __construct( $webhookURL )
	{
		$this->addHeader( 'Content-Type', 'application/json' );
		$this->url = $webhookURL;
	}
}
