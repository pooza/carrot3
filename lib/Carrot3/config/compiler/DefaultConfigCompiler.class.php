<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.compiler
 */

namespace Carrot3;

/**
 * 規定設定コンパイラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DefaultConfigCompiler extends ConfigCompiler {
	public function execute (ConfigFile $file) {
		// $file->serialize()が使用できないケースがある為、直接シリアライズ
		if ($this->controller->getAttribute($file, $file->getUpdateDate()) === null) {
			$this->controller->setAttribute($file, $this->getContents($file->getResult()));
		}

		$this->clearBody();
		$line = sprintf('return %s;', self::quote($this->controller->getAttribute($file)));
		$this->putLine($line);
		return $this->getBody();
	}

	/**
	 * 設定配列をシリアライズできる内容に修正
	 *
	 * @access protected
	 * @param mixed[] $config 対象
	 * @return mixed[] 変換後
	 */
	protected function getContents ($config) {
		return $config;
	}
}
