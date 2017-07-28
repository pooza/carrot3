<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * 定数関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_const ($params, &$smarty) {
	return (new C\ConstantHandler)[$params['name']];
}

