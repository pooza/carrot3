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
		$serials = new SerializeHandler;
		if ($serials->getAttribute($file, $file->getUpdateDate()) === null) {
			$serials->setAttribute($file, $this->getContents($file->getResult()));
		}

		$this->clearBody();
		$line = sprintf('return %s;', self::quote($serials[$file]));
		$this->putLine($line);
		return $this->getBody();
	}

	/**
	 * 設定配列をシリアライズできる内容に修正
	 *
	 * @access protected
	 * @param iterable $config 対象
	 * @return Tuple 変換後
	 */
	protected function getContents (iterable $config) {
		return $config;
	}
}
