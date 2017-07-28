<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.compiler
 */

namespace Carrot3;

/**
 * ディレクトリレイアウト設定コンパイラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class LayoutConfigCompiler extends ConfigCompiler {
	public function execute (ConfigFile $file) {
		$this->clearBody();
		$this->putLine('return [');
		foreach ($file->getResult() as $name => $params) {
			$this->putLine(sprintf('  %s => [', self::quote($name)));
			foreach ($params as $key => $value) {
				$this->putLine(parent::replaceConstants(
					sprintf('    %s => %s,', self::quote($key), self::quote($value))
				));
			}
			$this->putLine('  ],');
		}
		$this->putLine('];');
		return $this->getBody();
	}
}

