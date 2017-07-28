<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3 as C;

/**
 * 動画関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_movie ($params, &$smarty) {
	$params = C\Tuple::create($params);
	if (!$file = C\MovieFile::search($params)) {
		return null;
	}
	if (C\StringUtils::isBlank($params['href_prefix'])) {
		$finder = new C\RecordFinder($params);
		if ($record = $finder->execute()) {
			$url = C\FileUtils::createURL('movies');
			$url['path'] .= $record->getTable()->getDirectory()->getName() . '/';
			$params['href_prefix'] = $url->getContents();
		}
	}

	switch ($mode = C\StringUtils::toLower($params['mode'])) {
		case 'size':
			return $file['pixel_size'];
		case 'width':
		case 'height':
		case 'height_full':
		case 'pixel_size':
		case 'seconds':
		case 'duration':
		case 'type':
			return $file[$mode];
		default:
			return $file->createElement($params, $smarty->getUserAgent())->getContents();
	}
}

