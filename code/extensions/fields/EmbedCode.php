<?php
namespace Modular\Fields;

use TextField;

class EmbedCode extends Fields {
	const EmbedCodeFieldName = 'EmbedCode';
	const EmbedCodeOption    = 'EmbedCode';

	private static $db = [
		self::EmbedCodeFieldName => 'Text'
	];

	public function cmsFields() {
		return [
			new TextField(self::EmbedCodeFieldName)
		];
	}
}