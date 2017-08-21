<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage css
 */

namespace Carrot3;

/**
 * スタイルセット
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class StyleSet extends DocumentSet {

	/**
	 * 書類のクラス名を返す
	 *
	 * @access public
	 * @return string $name 書類のクラス名
	 */
	public function getDocumentClass () {
		return 'CSSFile';
	}

	/**
	 * ディレクトリ名を返す
	 *
	 * @access protected
	 * @return string ディレクトリ名
	 */
	protected function getDirectoryName () {
		return 'css';
	}

	/**
	 * HTML要素を返す
	 *
	 * @access public
	 * @return HTMLElement
	 */
	public function createElement () {
		$element = new HTMLElement('link');
		$element->setEmptyElement(true);
		$element->setAttribute('rel', 'stylesheet');
		$element->setAttribute('href', $this->getURL()->getContents());
		return $element;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('スタイルセット "%s"', $this->getName());
	}
}

