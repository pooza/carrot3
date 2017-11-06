<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage js
 */

namespace Carrot3;

/**
 * JavaScriptセット
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ScriptSet extends DocumentSet {

	/**
	 * 書類のクラス名を返す
	 *
	 * @access public
	 * @return string $name 書類のクラス名
	 */
	public function getDocumentClass () {
		return 'JavaScriptFile';
	}

	/**
	 * ディレクトリ名を返す
	 *
	 * @access protected
	 * @return string ディレクトリ名
	 */
	protected function getDirectoryName () {
		return 'js';
	}

	/**
	 * HTML要素を返す
	 *
	 * @access public
	 * @return HTMLElement
	 */
	public function createElement () {
		$element = new ScriptElement;
		$element->setAttribute('src', $this->getURL()->getContents());
		return $element;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('JavaScriptセット "%s"', $this->getName());
	}
}
