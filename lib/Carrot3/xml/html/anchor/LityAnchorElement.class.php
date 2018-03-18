<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html.anchor
 */

namespace Carrot3;

/**
 * Lityへのリンク
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LityAnchorElement extends ImageAnchorElement {
	private $width;
	private $height;

	/**
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct (string $name = null, UserAgent $useragent = null) {
		parent::__construct($name, $useragent);
		$this->setAttribute('data-lity', 'data-lity');
	}
}
