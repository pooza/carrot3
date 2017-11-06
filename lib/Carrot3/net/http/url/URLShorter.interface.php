<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.url
 */

namespace Carrot3;

/**
 * URL短縮機能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface URLShorter {

	/**
	 * 短縮URLを返す
	 *
	 * @access public
	 * @param HTTPRedirector $url 対象URL
	 * @return HTTPURL 短縮URL
	 */
	public function getShortURL (HTTPRedirector $url);
}
