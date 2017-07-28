<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;
use \Carrot3\HTTPURL;
use \Carrot3\URL;

/**
 * リンクターゲット修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_link_target ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)
		&& ($url = URL::create($value))
		&& ($url instanceof HTTPURL)) {

		if ($url->isForeign()) {
			return '_blank';
		}
		return '_self';
	}
}
