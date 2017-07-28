<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.compiler
 */

namespace Carrot3;

/**
 * オブジェクト登録設定コンパイラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ObjectRegisterConfigCompiler extends ConfigCompiler {
	public function execute (ConfigFile $file) {
		$this->clearBody();
		$this->putLine('return [');
		foreach ($file->getResult() as $values) {
			$values = Tuple::create($values);
			if (StringUtils::isBlank($values['class'])) {
				throw new ConfigException($file . 'で、クラス名が指定されていません。');
			}

			$line = new StringFormat('  new %s(%s),');
			$line[] = $this->loader->getClass($values['class']);
			$line[] = self::quote(Tuple::create($values['params']));
			$this->putLine($line);
		}
		$this->putLine('];');
		return $this->getBody();
	}
}

