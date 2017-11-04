<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * メールアドレス変換修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_email2link ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof C\ParameterHolder) {
		return $value->getParameters();
	} else if (!C\StringUtils::isBlank($value)) {
		return mb_ereg_replace(
			'[-+._[:alnum:]]+@([-.[:alnum:]]+)+[[:alpha:]]+',
			'<a href="mailto:\\0">\\0</a>',
			$value
		);
	}
}

