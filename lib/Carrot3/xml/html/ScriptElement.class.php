<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.html
 */

namespace Carrot3;

/**
 * script要素
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ScriptElement extends HTMLElement {

	/**
	 * 本文を設定
	 *
	 * @access public
	 * @param mixed $body 本文
	 */
	public function setBody ($body = null) {
		if ($body instanceof MessageContainer) {
			$body = $body->getMessage();
		}

		require_once BS_LIB_DIR . '/jsmin.php';
		$body = StringUtils::convertEncoding($body, 'utf-8');
		$body = ltrim(\JSMin::minify($body));
		$this->body = $body;
		$this->contents = null;
	}
}
