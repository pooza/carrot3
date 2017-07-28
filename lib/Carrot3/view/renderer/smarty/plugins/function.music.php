<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty.plugins
 */

use \Carrot3\Tuple;
use \Carrot3\MusicFile;
use \Carrot3\StringUtils;
use \Carrot3\RecordFinder;
use \Carrot3\FileUtils;

/**
 * 楽曲関数
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
function smarty_function_music ($params, &$smarty) {
	$params = Tuple::create($params);
	if (!$file = MusicFile::search($params)) {
		return null;
	}

	switch ($mode = StringUtils::toLower($params['mode'])) {
		case 'seconds':
		case 'duration':
		case 'type':
			return $file[$mode];
		default:
			if (StringUtils::isBlank($params['href_prefix'])) {
				$finder = new RecordFinder($params);
				if ($record = $finder->execute()) {
					$url = FileUtils::createURL('musics');
					$url['path'] .= $record->getTable()->getDirectory()->getName() . '/';
					$params['href_prefix'] = $url->getContents();
				}
			}
			return $file->createElement($params)->getContents();
	}
}

