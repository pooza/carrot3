<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;

/**
 * サニタイズ修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_sanitize ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)) {
		$value = StringUtils::unsanitize($value);
		return StringUtils::sanitize($value);
	}
}

