<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;
use \Carrot3\URL;
use \Carrot3\HTTPURL;

/**
 * URLからホスト名を抽出する修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_host_name ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)
		&& ($url = URL::create($value))
		&& ($url instanceof HTTPURL)) {

		return $url['host']->getName();
	}
}
