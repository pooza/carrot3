<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTMLElementTest extends Test {
	public function execute () {
		$element = new HTMLElement('input');
		$element->setEmptyElement(true);
		$element->setAttribute('type', 'checkbox');
		$element->setAttribute('checked', 'checked');
		$this->assert('getContents', $element->getContents() == '<input type="checkbox" checked>');
	}
}
