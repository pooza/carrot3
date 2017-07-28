<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * video要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class VideoElement extends HTMLElement {

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		parent::__construct($name, $useragent);
		$this->setAttribute('controls', 'controls');
		$this->setAttribute('autobuffer', 'autobuffer');
		$this->createElement('p', 'HTML5 Videoに対応したブラウザをご利用ください。');
	}

	/**
	 * ソースを登録
	 *
	 * @access public
	 * @param HTTPRedirector $url メディアのURL
	 */
	public function registerSource (HTTPRedirector $url) {
		$element = $this->createElement('source');
		$element->setEmptyElement(true);
		$element->setAttribute('src', $url->getContents());
	}

	/**
	 * URLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url メディアのURL
	 */
	public function setURL (HTTPRedirector $url) {
		$this->registerSource($url);
	}
}

