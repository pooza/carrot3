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
	if (!$file = C\MusicFile::search($params)) {
		return null;
	}

	switch ($mode = C\StringUtils::toLower($params['mode'])) {
		case 'seconds':
		case 'duration':
		case 'type':
			return $file[$mode];
		default:
			if (C\StringUtils::isBlank($params['href_prefix'])) {
				$finder = new C\RecordFinder($params);
				if ($record = $finder->execute()) {
					$url = C\FileUtils::createURL('musics');
					$url['path'] .= $record->getTable()->getDirectory()->getName() . '/';
					$params['href_prefix'] = $url->getContents();
				}
			}
			return $file->createElement($params)->getContents();
	}
}

