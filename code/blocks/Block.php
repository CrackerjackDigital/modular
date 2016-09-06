<?php
namespace Modular\Blocks;

use Modular\Model;

/**
 * Class which represents a block which can be added to an Article, of types ( in display order ). The types in the grid dropdown are determined by
 * subclasses of this class, so there is no need e.g. for a 'BlockType' lookup or relationship.
 * 'Text',
 * 'Video',
 * 'Audio',
 * 'Images (gallery)',
 * 'Image (full width)',
 * 'Footnotes',
 * 'Links',
 * 'Download',
 * 'Pull Quote'
 */
class Block extends Model {
	private static $template = '';

	private static $summary_fields = [
		'BlockType' => 'Block Type'
	];

	public function BlockType() {
		return $this->i18n_singular_name();
	}

	/**
	 * @return string
	 */
	public static function block_class() {
		return get_called_class();
	}

	public function DisplayInSidebar() {
		return false;
	}

	public function DisplayInContent() {
		return true;
	}

	/**
	 * Ok so this makes Blocks a 'Model-View' but we already have that via ViewableData so run with it.
	 *
	 * @return \HTMLText
	 */
	public function forTemplate() {
		return $this->renderWith($this->templates());
	}

	protected function template() {
		return $this->config()->get('template') ?: $this->class;
	}

	protected function templates() {
		return [$this->template()];
	}

	/**
	 * Return the current page from Director.
	 *
	 * @return \Page
	 */
	public function CurrentPage() {
		/** @var \Page $parent */
		return \Director::get_current_page();
	}

	/**
	 * Return current pages ClassName.
	 * @return string
	 */
	public function PageClassName() {
		return \Director::get_current_page()->ClassName;
	}

}
