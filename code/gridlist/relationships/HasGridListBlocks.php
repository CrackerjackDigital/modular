<?php
namespace Modular\Relationships;

use Modular\GridList\Interfaces\ItemsSequencer;

/**
 * Add blocks manually to a grid list items at the start.
 *
 * @package Modular\GridList
 */
class HasGridListBlocks extends HasBlocks implements ItemsSequencer {
	const RelationshipName    = 'GridListBlocks';
	const RelatedClassName    = 'Modular\Blocks\Block';
	const GridFieldConfigName = 'Modular\GridField\HasGridListBlocksGridFieldConfig';

	/**
	 * Inserts manually added blocks at front of list.
	 *
	 * @param \ArrayList|\DataList $items
	 * @param                      $filters
	 * @param array                $parameters
	 */
	public function sequenceGridListItems(&$items, $filters, &$parameters = []) {
		$out = new \ArrayList();

		// reverse sort so insertFirst works
		$blocks = $this->related()->Sort('Sort desc');
		foreach ($blocks as $block) {
			$out->push($block);
		}
		foreach ($items as $item) {
			$out->push($item);
		}
		$items = $out;
	}

}