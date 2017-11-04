<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * URL省略修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_truncate_url ($value, $length = 16) {
	foreach (C\StringUtils::eregMatchAll('https?://[[:graph:]]+', $value) as $matches) {
		$value = str_replace($matches[0], C\StringUtils::truncate($matches[0], $length), $value);
	}
	return $value;
}

