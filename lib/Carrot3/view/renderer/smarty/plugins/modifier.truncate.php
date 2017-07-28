<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;

/**
 * truncate修飾子
 *
 * Smarty標準truncate演算子と恐らく互換、マルチバイト対応。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_truncate ($value, $length = 80, $suffix = '...') {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)) {
		return StringUtils::truncate($value, $length, $suffix);
	}
	return $value;
}

