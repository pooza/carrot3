<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * CSSキャッシュ関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_css ($params, &$smarty) {
	$params = C\Tuple::create($params);
	if (C\StringUtils::isBlank($params['name'])) {
		$params['name'] = 'carrot';
	}
	return (new C\StyleSet($params['name']))->createElement()->getContents();
}
