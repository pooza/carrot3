<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\StringUtils;
use \Carrot3\StyleSet;

/**
 * CSSキャッシュ関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_css_cache ($params, &$smarty) {
	$params = Tuple::create($params);
	if (StringUtils::isBlank($params['name'])) {
		$params['name'] = 'carrot';
	}

	$styleset = new StyleSet($params['name']);
	return $styleset->createElement()->getContents();
}

