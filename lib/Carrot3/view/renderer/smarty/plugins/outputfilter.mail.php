<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * メール文面用フィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_outputfilter_mail ($source, &$smarty) {
	$mime = new C\Mail;
	$mime->setContents($source);
	foreach ($mime->getHeaders() as $header) {
		$smarty->getHeaders()->setParameter($header->getName(), $header->getEntity());
	}
	$source = $mime->getBody();
	return $source;
}

