<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;

/**
 * 電話番号変換修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_tel2link ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)) {
		return mb_ereg_replace(
			'([[:digit:]]{2,4})-([[:digit:]]{2,4})-([[:digit:]]{3,4})',
			'<a href="tel:\\1\\2\\3">\\0</a>',
			$value
		);
	}
}

