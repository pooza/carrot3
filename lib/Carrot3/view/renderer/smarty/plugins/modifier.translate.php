<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * 翻訳修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_translate ($value, $dictionary = null, $lang = null) {
	return C\TranslateManager::getInstance()->execute($value, $dictionary, $lang);
}
