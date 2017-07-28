<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.file
 */

namespace Carrot3;

/**
 * ルート設定ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RootConfigFile extends ConfigFile {
	private $compiler;

	/**
	 * コンパイラを返す
	 *
	 * @access public
	 * @return ConfigCompiler コンパイラ
	 */
	public function getCompiler () {
		if (!$this->compiler) {
			$this->compiler = new ObjectRegisterConfigCompiler;
		}
		return $this->compiler;
	}
}

