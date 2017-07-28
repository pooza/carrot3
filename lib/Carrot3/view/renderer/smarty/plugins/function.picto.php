<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Pictogram;

/**
 * ケータイ絵文字関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_picto ($params, &$smarty) {
	$pictogram = Pictogram::getInstance($params['name']);
	return $pictogram->getContents();
}

