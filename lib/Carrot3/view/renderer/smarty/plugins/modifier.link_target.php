<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * リンクターゲット修飾子
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_link_target ($value) {
	if (($url = C\URL::create($value)) && ($url instanceof C\HTTPURL)) {
		if ($url->isForeign()) {
			return '_blank';
		}
		return '_self';
	}
}
