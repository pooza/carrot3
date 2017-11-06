<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * バイナリサイズ修飾子
 *
 * ファイルサイズ等に利用。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_modifier_binary_size_format ($value) {
	return C\Numeric::getBinarySize($value);
}
