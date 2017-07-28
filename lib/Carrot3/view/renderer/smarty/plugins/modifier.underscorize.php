<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * underscorize修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_underscorize ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof C\ParameterHolder) {
		return $value->getParameters();
	} else if (!C\StringUtils::isBlank($value)) {
		return C\StringUtils::underscorize($value);
	}
	return $value;
}

