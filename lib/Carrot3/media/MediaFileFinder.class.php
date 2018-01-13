<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ファイル検索
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MediaFileFinder extends FileFinder {

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $file ファイル名、File等
	 * @return File 最初にマッチしたファイル
	 */
	public function execute ($file) {
		if ($file = parent::execute($file)) {
			switch ($file->getMainType()) {
				case 'image':
					return new ImageFile($file->getPath());
				case 'video':
					return new MovieFile($file->getPath());
				case 'audio':
					return new MusicFile($file->getPath());
			}
		}
	}

	/**
	 * 探す
	 *
	 * @access public
	 * @param mixed $file パラメータ配列、File、ファイルパス文字列
	 * @return File ファイル
	 * @static
	 */
	static public function search ($file) {
		if (is_array($file) || ($file instanceof ParameterHolder)) {
			$params = Tuple::create($file);
			if (StringUtils::isBlank($params['src'])) {
				if ($record = (new RecordFinder($params))->execute()) {
					if ($attachment = $record->getAttachment($params['size'])) {
						return (new self)->execute($attachment);
					}
				}
			} else {
				return (new self)->execute($file);
			}
		}
		return (new self)->execute($file);
	}
}
