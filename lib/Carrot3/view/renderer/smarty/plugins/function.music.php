<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * 楽曲関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_music ($params, &$smarty) {
	$params = C\Tuple::create($params);
	if (!$file = (new C\MediaFileFinder)->execute($params)) {
		return null;
	}
	if ($record = (new C\RecordFinder($params))->execute()) {
		$params['href_prefix'] = C\FileUtils::createURL('musics')->getContents();
	}
	return $file->createElement($params, $smarty->getUserAgent())->getContents();
}
