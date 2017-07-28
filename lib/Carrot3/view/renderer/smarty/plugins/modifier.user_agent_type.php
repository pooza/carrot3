<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * UserAgent種別修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_user_agent_type ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof C\ParameterHolder) {
		return $value->getParameters();
	} else if (!C\StringUtils::isBlank($value)) {
		if ($useragent = C\UserAgent::create($value)) {
			return $useragent->getType();
		}
	}
}

