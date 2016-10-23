<?php

/*
 * This file is part of Huxtable\Bot\Slack
 */
namespace Huxtable\Bot\Slack;

use Huxtable\Core;
use Huxtable\Core\File;
use Huxtable\Core\HTTP;

class Slack
{
	/**
	 * @param	string	$message
	 * @return	Huxtable\Core\HTTP\Response
	 */
	public function postMessage( $webhookURL, Message $message )
	{
		$request = new Request( $webhookURL );
		$request = $message->populateRequest( $request );

		$response = Core\HTTP::post( $request );

		return $response;
	}
}
