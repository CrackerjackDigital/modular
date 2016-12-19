<?php
namespace Modular\Fields;

use Modular\GridField\GridFieldConfig;
use Modular\GridField\GridFieldOrderableRows;
use Modular\Model;

/**
 * A field that manages relationships between the extended model and other models.
 *
 * @package Modular\Fields
 */
abstract class Relationship extends Field {
	const ShowAsGridField     = 'grid';
	const RelationshipName    = '';
	const RelatedClassName    = '';
	const GridFieldConfigName = 'Modular\GridField\GridFieldConfig';

	const GridFieldOrderableRowsFieldName = GridFieldOrderableRows::SortFieldName;

	// wether to show the field as a GridField or a TagField
	private static $show_as = self::ShowAsGridField;

	// can related models be in an order so a GridFieldOrderableRows component is added?
	private static $sortable = true;

	// allow new related models to be created
	private static $allow_add_new = true;

	// show autocomplete existing filter
	private static $autocomplete = true;

	private static $gridfield_config_class = self::GridFieldConfigName;

	/**
	 * Return a gridfield
	 *
	 * @return array
	 */
	public function cmsFields() {
		return $this->gridFields();
	}

	/**
	 * Return field(s) to show a gridfield in the CMS, or a 'please save...' prompt if the model hasn't been saved
	 *
	 * @return array
	 */
	protected function gridFields() {
		// could get a null gridfield so filter it out
		return array_filter(
			$this()->isInDB()
				? [$this->gridField()]
				: [$this->saveMasterHint()]
		);
	}

	public static function sortable() {
		return static::config()->get('sortable');
	}

	public static function field_name($suffix = '') {
		return static::RelationshipName . $suffix;
	}

	/**
	 * Returns the related class name optionally appended by '.fieldName', so e.g. when used as a filter in a relationship you will get full
	 * namespaced class for the relationship column.
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public static function related_class_name($fieldName = '') {
		return static::RelatedClassName . ($fieldName ? ".$fieldName" : '');
	}

	public static function relationship_name($fieldName = '') {
		return static::RelationshipName . ($fieldName ? ".$fieldName" : '');
	}

	/**
	 * Return a GridField configured for editing attached MediaModels. If the master record is in the database
	 * then also add GridFieldOrderableRows (otherwise complaint re UnsavedRelationList not being a DataList happens).
	 *
	 * @param string|null $relationshipName
	 * @param string|null $configClassName name of grid field configuration class otherwise one is manufactured
	 * @return \GridField
	 */
	protected function gridField($relationshipName = null, $configClassName = null) {
		$relationshipName = $relationshipName ?: static::RelationshipName;

		$config = $this->gridFieldConfig($relationshipName, $configClassName);

		if ($this()->hasMethod($relationshipName)) {
			// we need to guard this for when changing page types in CMS
			$list = $this()->$relationshipName();
			/** @var \GridField $gridField */

			return \GridField::create(
				$relationshipName,
				$relationshipName,
				$list,
				$config
			);

		}
		return null;
	}

	/**
	 * Returns a configured GridFieldConfig
	 *
	 * @param string $relationshipName if not supplied then static.RelationshipName via relationship_name()
	 * @param string $configClassName  if not supplied then static.GridFieldConfigName or one is guessed, or base is used
	 * @return GridFieldConfig
	 */
	protected function gridFieldConfig($relationshipName = '', $configClassName = '') {
		$relationshipName = $relationshipName
			?: static::relationship_name();

		$configClassName = $configClassName
			?: static::gridfield_config_class();

		/** @var GridFieldConfig $config */
		$config = $configClassName::create();
		$config->setSearchPlaceholder(

			singleton(static::RelatedClassName)->fieldDecoration(
				$relationshipName,
				'SearchPlaceholder',
				"Link existing {plural} by Title"
			)
		);

		if ($this()->isInDB()) {
			// only add if this record is already saved
			$config->addComponent(
				new GridFieldOrderableRows(static::GridFieldOrderableRowsFieldName)
			);
		}

		if (!$this->config()->get('allow_add_new')) {
			$config->removeComponentsByType(GridFieldConfig::ComponentAddNewButton);
		}
		if (!$this->config()->get('autocomplete')) {
			$config->removeComponentsByType(GridFieldConfig::ComponentAutoCompleter);
		}

		return $config;
	}

	/**
	 * When a page with blocks is published we also need to publish blocks. Blocks should also publish their 'sub' blocks.
	 */
	public function onAfterPublish() {
		/** @var Model|\Versioned $block */
		foreach ($this()->{static::RelationshipName}() as $block) {
			if ($block->hasExtension('Versioned')) {
				$block->publish('Stage', 'Live', false);
			}
		}
	}

	/**
	 * Returns configured or manufactured class name
	 * falling back to 'Modular\GridField\GridFieldConfig' if class doesn't exist.
	 *
	 * @return string
	 */
	protected static function gridfield_config_class() {
		$className = static::config()->get('gridfield_config_class')
			?: get_called_class() . 'GridFieldConfig';

		if (!\ClassInfo::exists($className)) {
			$className = GridFieldConfig::class_name();
		}
		return $className;
	}
}
