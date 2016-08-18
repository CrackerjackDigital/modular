<?php
namespace Modular\Fields;

use ArrayList;
use FormField;

/**
 * HasAudioField
 *
 * @method Media
 */
class Audio extends Media {

	private static $allowed_audio_files = 'audio';

	private static $upload_folder = 'audio';

	/**
	 * Return a list with only item being the single related image.
	 *
	 * @return \ArrayList
	 */
	public function Medias() {
		return new ArrayList(array_filter([$this->Audio()]));
	}

	/**
	 * Return the single related image
	 *
	 * @return Image|null
	 */
	public function Audio() {
		return $this()->Media();
	}

	public static function allowed_files() {
		return 'allowed_audio_files';
	}

}