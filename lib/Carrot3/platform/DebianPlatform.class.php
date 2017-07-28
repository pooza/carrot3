<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage platform
 */

namespace Carrot3;

/**
 * Debianプラットフォーム
 *
 * Ubuntu等を含む、Debian系。
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DebianPlatform extends LinuxPlatform {

	/**
	 * ディレクトリを返す
	 *
	 * @access public
	 * @param string $name ディレクトリ名
	 * @return Directory ディレクトリ
	 */
	public function getDirectory ($name) {
		$constants = new ConstantHandler($name);
		foreach ([$this->getName(), 'linux', 'default'] as $suffix) {
			if (!StringUtils::isBlank($path = $constants['dir_' . $suffix])) {
				return new Directory($path);
			}
		}
	}
}

