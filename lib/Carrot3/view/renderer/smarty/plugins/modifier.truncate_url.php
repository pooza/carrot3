<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;

/**
 * URL省略修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_truncate_url ($value, $length = 16) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)) {
		foreach (StringUtils::eregMatchAll('https?://[[:graph:]]+', $value) as $matches) {
			$value = str_replace($matches[0], StringUtils::truncate($matches[0], $length), $value);
		}
		return $value;
	}
}

