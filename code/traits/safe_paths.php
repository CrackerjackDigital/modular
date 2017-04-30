<?php
namespace Modular\Traits;

use Controller;

trait safe_paths {
	/**
	 * @param null $className
	 *
	 * @return \Config_ForClass
	 */
	abstract public function config($className = null);

	/**
	 * Return array of paths considered safe to write to, relative to DOCUMENT ROOT.
	 *
	 * @return array
	 */
	public static function safe_paths() {
		return array_unique( static::config()->get( 'safe_paths' ) ?: [] );
	}

	/**
	 * Check if a path begins with a safe path so is OK to write to.
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function in_safe_path( $path ) {
		if ( substr( $path, 0, 1 ) == '/'|| substr( $path, 0, 2 ) == '..' ) {
			// make absolute
			$path = Controller::join_links(BASE_PATH, $path);
		} else {
			$path = Controller::join_links(ASSETS_PATH, $path);
		}
		$real = realpath( $path );
		if ( $real ) {
			foreach ( static::safe_paths() as $safePath ) {
				$safe = Controller::join_links(BASE_PATH, trim( $safePath, '/' ));

				if ( substr( $real, 0, strlen( $safe ) ) == $safe ) {
					return true;
				}
			}
		}

		return false;
	}

}