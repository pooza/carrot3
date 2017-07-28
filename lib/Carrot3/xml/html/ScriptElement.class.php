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
	 * @access public
	 * @param string $name 要素の名前
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function __construct ($name = null, UserAgent $useragent = null) {
		parent::__construct($name, $useragent);
		if (!BS_VIEW_HTML5) {
			$this->setAttribute('type', 'text/javascript');
			if (!$this->getUserAgent()->isMobile()) {
				$this->setAttribute('charset', 'utf-8');
			}
		}
	}

	/**
	 * 本文を設定
	 *
	 * @access public
	 * @param string $body 本文
	 */
	public function setBody ($body = null) {
		if ($body instanceof StringFormat) {
			$body = $body->getContents();
		}

		require_once BS_LIB_DIR . '/jsmin.php';
		$body = StringUtils::convertEncoding($body, 'utf-8');
		$body = ltrim(\JSMin::minify($body));
		$this->body = $body;
		$this->contents = null;
	}
}

