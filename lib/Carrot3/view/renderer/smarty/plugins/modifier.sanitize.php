<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * サニタイズ修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_sanitize ($value) {
	return C\StringUtils::sanitize(
		C\StringUtils::unsanitize($value)
	);
}

