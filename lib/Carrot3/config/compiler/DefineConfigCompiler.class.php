<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.compiler
 */

namespace Carrot3;

/**
 * 定数設定コンパイラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DefineConfigCompiler extends ConfigCompiler {
	public function execute (ConfigFile $file) {
		$this->clearBody();
		$this->putLine('$constants = [');

		foreach ($this->getConstants($file->getResult()) as $key => $value) {
			$line = sprintf('  %s => %s,', self::quote($key), self::quote($value));
			$line = parent::replaceConstants($line);
			$this->putLine($line);
		}

		$this->putLine('];');
		$this->putLine('foreach ($constants as $name => $value) {');
		$this->putLine('  if (!defined($name)) {define($name, $value);}');
		$this->putLine('}');
		return $this->getBody();
	}

	private function getConstants ($arg, $prefix = ConstantHandler::PREFIX) {
		if ($arg instanceof ParameterHolder) {
			if ($arg->hasParameter(0)) {
				return [StringUtils::toUpper($prefix) => $arg->join(',')];
			} else {
				$constants = [];
				foreach ($arg as $key => $value) {
					$constants += $this->getConstants($value, $prefix . '_' . $key);
				}
				return $constants;
			}
		} else {
			return [StringUtils::toUpper($prefix) => $arg];
		}
	}
}
