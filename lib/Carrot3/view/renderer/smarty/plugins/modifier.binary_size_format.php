<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\ParameterHolder;
use \Carrot3\StringUtils;
use \Carrot3\Numeric;

/**
 * バイナリサイズ修飾子
 *
 * ファイルサイズ等に利用。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_binary_size_format ($value) {
	if (is_array($value)) {
		return $value;
	} else if ($value instanceof ParameterHolder) {
		return $value->getParameters();
	} else if (!StringUtils::isBlank($value)) {
		return Numeric::getBinarySize($value);
	}
}

