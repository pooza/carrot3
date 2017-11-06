<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html.anchor
 */

namespace Carrot3;

/**
 * a要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AnchorElement extends HTMLElement {

	/**
	 * タグ名を返す
	 *
	 * @access public
	 * @return string タグ名
	 */
	public function getTag () {
		return 'a';
	}

	/**
	 * URLを設定
	 *
	 * @access public
	 * @param mixed $url
	 */
	public function setURL ($url) {
		if ($url instanceof HTTPRedirector) {
			$url = $url->getURL()->getContents();
		} else if ($url instanceof URL) {
			$url = $url->getContents();
		}
		$this->setAttribute('href', $url);
	}

	/**
	 * リンク先ターゲットを _blank にする
	 *
	 * @access public
	 * @param boolean $flag _blankにするならTrue
	 */
	public function setTargetBlank ($flag) {
		if (!!$flag) {
			$this->setAttribute('target', '_blank');
		}
	}

	/**
	 * 対象にリンクを設定
	 *
	 * @access public
	 * @param XMLElement $element 対象要素
	 * @param HTTPRedirector $url リンク先
	 * @return AnchorElement 自身
	 */
	public function link (XMLElement $element, HTTPRedirector $url) {
		$this->addElement($element);
		$this->setURL($url);
		$this->setTargetBlank($url->isForeign());
		return $this;
	}
}
