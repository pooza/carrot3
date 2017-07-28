<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * 日付書式化修飾子
 *
 * Smarty標準のdate_format修飾子と互換。
 * strftime関数に加え、date関数でも処理する。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_date_format ($value, $format = 'Y/m/d H:i:s') {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof C\ParameterHolder) {
		return $value->getParameters();
	} else if (!C\StringUtils::isBlank($value)) {
		if ($date = C\Date::create($value)) {
			return $date->format($format);
		} else {
			return $value;
		}
	}
}

