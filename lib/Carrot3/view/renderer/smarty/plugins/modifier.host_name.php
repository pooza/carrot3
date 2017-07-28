<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * URLからホスト名を抽出する修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_host_name ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof C\ParameterHolder) {
		return $value->getParameters();
	} else if (!C\StringUtils::isBlank($value)
		&& ($url = C\URL::create($value))
		&& ($url instanceof C\HTTPURL)) {

		return $url['host']->getName();
	}
}
