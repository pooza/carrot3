<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;

/**
 * メールアドレス変換修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_email2link ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)) {
		return mb_ereg_replace(
			'[-+._[:alnum:]]+@([-._[:alnum:]]+)+[[:alpha:]]+',
			'<a href="mailto:\\0">\\0</a>',
			$value
		);
	}
}

