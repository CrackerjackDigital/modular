<?php
namespace Modular\Behaviours;

use Modular\Fields\Field;
use Modular\Fields\InternalLink;
use Modular\Fields\ExternalLink;
use ClassInfo;
use DropdownField;
use FormField;

/**
 * Binds InternalLink and ExternalLink fields with a LinkType field and attaches associated behaviours (display logic etc).
 *
 * @package Modular\Behaviours
 */
class InternalOrExternalLink extends Field {
	const LinkTypeFieldName = 'LinkType';

	private static $enum_values = [
		\Modular\Fields\InternalLink::InternalLinkOption,
		\Modular\Fields\ExternalLink::ExternalLinkOption,
	];

	/**
	 * Returns text of link, either as entered for External or generated from Internal. If Internal an target page
	 * isn't found then returns LinkAttributeExtension.InternalLink.MissingTarget message e.g. '[linked page not found]' type message
	 *
	 * @return string
	 */
	public function ResolvedLink() {
		if ($this->IsExternal()) {
			$externalLink = $this()->ExternalLink;
			if (!\Director::is_absolute_url($externalLink)) {
				$link = 'http://' . $externalLink;
			} else {
				$link = $externalLink;
			}
		} elseif ($this()->InternalLink()) {
			$link = $this()->InternalLink()->Link();
		} else {
			$link = $this->fieldDecoration(
				'InternalLink',
				'Missing',
				'[linked page not found or not set]'
			);
		}
		return $link;
	}

	/**
	 * Usefull for templates to indicate external links
	 *
	 * @return bool
	 */
	public function IsExternal() {
		return $this()->LinkType == ExternalLink::ExternalLinkOption;
	}

	/**
	 * Usefull for templates to indicate indicate links
	 *
	 * @return bool
	 */
	public function IsInternal() {
		return $this()->LinkType == InternalLink::InternalLinkOption;
	}

	/**
	 * Add enum definition to config.db for LinkTypeFieldName with field names from Internal and External link fields as options. These are taken via
	 * config.enum_values so could be overridden.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		$values = implode(',', $this->config()->get('enum_values'));

		return array_merge_recursive(
			$parent,
			[
				'db' => [
					static::LinkTypeFieldName => 'enum("' . $values . '")'
				],
			]
		);
	}

	public function cmsFields() {
		return [
			new DropdownField(self::LinkTypeFieldName, 'Link type', $this->linkOptions())
		];
	}

	protected function linkOptions() {
		return [
			InternalLink::InternalLinkOption => $this->fieldDecoration(InternalLink::InternalLinkFieldName, 'Label', 'Internal link'),
			ExternalLink::ExternalLinkOption => $this->fieldDecoration(ExternalLink::ExternalLinkFieldName, 'Label', 'External link'),
		];
	}

	/**
	 * Show/hide fields using display_logic depending on the LinkType field added by this extension.
	 *
	 * @param \FormField $field
	 * @param array      $allFieldConstraints
	 */
	public function customFieldConstraints(FormField $field, array $allFieldConstraints) {
		if (ClassInfo::exists('DisplayLogicCriteria')) {
			$fieldName = $field->getName();

			if ($fieldName == InternalLink::InternalLinkFieldName) {

				$field->hideUnless(self::LinkTypeFieldName)
					->isEqualTo(InternalLink::InternalLinkOption);

			} elseif ($fieldName == ExternalLink::ExternalLinkFieldName) {

				$field->hideUnless(self::LinkTypeFieldName)
					->isEqualTo(ExternalLink::ExternalLinkOption);

			}
		}
	}
}