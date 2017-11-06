<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * エンコード強制変換フィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_outputfilter_encoding ($source, &$smarty) {
	return C\StringUtils::convertEncoding($source, $smarty->getEncoding(), 'utf-8');
}
