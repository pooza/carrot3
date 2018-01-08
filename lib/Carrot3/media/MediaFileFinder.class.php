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
		$file = parent::execute($file);
		switch ($type = $file->getMainType()) {
			case 'image':
				return new ImageFile($file->getPath());
			case 'video':
				return new MovieFile($file->getPath());
			case 'audio':
				return new MusicFile($file->getPath());
			default:
				$message = new StringFormat('ファイルタイプ %s が不正です。');
				$message[] = $type;
				throw new MediaException($message);
		}
	}
}
