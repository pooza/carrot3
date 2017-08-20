<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * JavaScriptキャッシュ関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_js ($params, &$smarty) {
	$params = C\Tuple::create($params);
	if (C\StringUtils::isBlank($params['name'])) {
		$params['name'] = 'carrot';
	}
	return (new C\ScriptSet($params['name']))->createElement()->getContents();
}

