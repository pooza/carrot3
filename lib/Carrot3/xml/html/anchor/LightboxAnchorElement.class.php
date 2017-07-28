<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html.anchor
 */

namespace Carrot3;

/**
 * Lightboxへのリンク
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LightboxAnchorElement extends ImageAnchorElement {

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		parent::__construct($name, $useragent);
		$this->setAttribute('rel', 'lightbox');
	}
}

