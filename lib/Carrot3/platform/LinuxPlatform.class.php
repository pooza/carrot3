<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage platform
 */

namespace Carrot3;

/**
 * Linuxプラットフォーム
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LinuxPlatform extends Platform {

	/**
	 * ファイルの内容から、メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function analyzeFile (File $file) {
		return rtrim(exec('file -bi ' . $file->getPath()));
	}
}
