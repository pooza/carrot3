<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * iframe要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class InlineFrameElement extends HTMLElement {

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		parent::__construct($name, $useragent);
		$this->setAttribute('frameborder', 0);
		$this->setAttribute('scrolling', 'no');
		$this->createElement('p', 'インラインフレームに対応したブラウザをご利用ください。');
	}

	/**
	 * タグ名を返す
	 *
	 * @access public
	 * @return string タグ名
	 */
	public function getTag () {
		return 'iframe';
	}

	/**
	 * URLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url メディアのURL
	 */
	public function setURL (HTTPRedirector $url) {
		$this->setAttribute('src', $url->getURL()->getContents());
	}
}

