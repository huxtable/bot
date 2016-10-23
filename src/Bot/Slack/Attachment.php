<?php

/*
 * This file is part of Huxtable\Bot\Slack
 */
namespace Huxtable\Bot\Slack;

class Attachment implements \JsonSerializable
{
	/**
	 * @var	string
	 */
	protected $color;

	/**
	 * @var	array
	 */
	protected $fields=[];

	/**
	 * @var	string
	 */
	protected $title;

	/**
	 * @param
	 * @return	void
	 */
	public function __construct( $title )
	{
		$this->title = $title;
	}

	/**
	 * @param	string	$title
	 * @param	string	$value
	 * @param	boolean	$short
	 */
	public function addField( $title, $value, $short=false )
	{
		$field['title'] = $title;
		$field['value'] = $value;
		if( $short != false )
		{
			$field['short'] = $short;
		}

		$this->fields[] = $field;
	}

	/**
	 * @param	string	$color
	 */
	public function setColor( $color )
	{
		$this->color = $color;
	}

	/**
	 * @return	array
	 */
	public function jsonSerialize()
	{
		$data = [];

		$data['title'] = $this->title;

		if( !is_null( $this->color ) )
		{
			$data['color'] = $this->color;
		}
		if( !empty( $this->fields ) )
		{
			$data['fields'] = $this->fields;
		}

		return $data;
	}
}
