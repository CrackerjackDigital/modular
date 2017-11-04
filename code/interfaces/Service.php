<?php

namespace Modular\Interfaces;

interface Service extends Executable {

	/**
	 * Return a configured instance of the service.
	 *
	 * @param null|mixed $options to create instance with
	 * @param string     $env     to run in (e.g. for testing force a particular environment)
	 *
	 * @return Service
	 */
	public static function get( $options = null, $env = '' );

}