<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage platform
 */

namespace Carrot3;

/**
 * FreeBSDプラットフォーム
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FreeBSDPlatform extends Platform {

	/**
	 * ファイルをリネーム
	 *
	 * @access public
	 * @param DirectoryEntry $file 対象ファイル
	 * @param string $path リネーム後のパス
	 */
	public function renameFile (DirectoryEntry $file, $path) {
		if (!@rename($file->getPath(), $path)) {
			throw new FileException($this . 'を移動できません。');
		}
	}
}
