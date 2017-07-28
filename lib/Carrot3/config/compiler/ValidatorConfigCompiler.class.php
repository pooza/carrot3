<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.compiler
 */

namespace Carrot3;

/**
 * バリデータ設定コンパイラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ValidatorConfigCompiler extends ConfigCompiler {
	private $methods;
	private $fields;
	private $validators;

	public function execute (ConfigFile $file) {
		$this->clearBody();
		$this->parse($file);

		$this->putLine('$manager = \\Carrot3\\ValidateManager::getInstance();');
		$this->putLine('$request = \\Carrot3\\Request::getInstance();');
		$line = new StringFormat('if (in_array($request->getMethod(), %s)) {');
		$line[] = self::quote($this->methods->getParameters());
		$this->putLine($line);
		foreach ($this->fields as $name => $validators) {
			foreach ($validators as $validator) {
				$line = new StringFormat('  $manager->register(%s, new %s(%s));');
				$line[] = self::quote($name);
				$line[] = $this->loader->getClass($this->validators[$validator]['class']);
				$line[] = self::quote(Tuple::create($this->validators[$validator]['params']));
				$this->putLine($line);
			}
		}
		$this->putLine('}');

		return $this->getBody();
	}

	private function parse (ConfigFile $file) {
		$configure = ConfigManager::getInstance();
		$this->validators = Tuple::create();
		$this->validators->setParameters($configure->compile('validator/carrot'));
		$this->validators->setParameters($configure->compile('validator/application'));

		$server = $this->controller->getHost();
		if ($config = ConfigManager::getConfigFile('validator/' . $server->getName())) {
			$this->validators->setParameters($configure->compile($config));
		}

		$config = Tuple::create($file->getResult());
		$this->parseMethods(Tuple::create($config['methods']));
		$this->parseFields(Tuple::create($config['fields']));
		$this->parseValidators(Tuple::create($config['validators']));
	}

	private function parseMethods (Tuple $config) {
		if (!$config->count()) {
			$config[] = 'GET';
			$config[] = 'POST';
		}

		$this->methods = Tuple::create();
		foreach ($config as $method) {
			$method = StringUtils::toUpper($method);
			if (!HTTPRequest::isValidMethod($method)) {
				throw new ConfigException($method . 'は正しくないメソッドです。');
			}
			$this->methods[] = $method;
		}
	}

	private function parseFields (Tuple $config) {
		$this->fields = Tuple::create();
		foreach ($config as $name => $field) {
			$field = Tuple::create($field);

			$this->fields[$name] = Tuple::create();
			if ($field['file']) {
				$this->fields[$name][] = 'file';
			} else {
				$this->fields[$name][] = 'string';
			}
			if ($field['required']) {
				$this->fields[$name][] = 'empty';
			}
			$this->fields[$name]->merge($field['validators']);
			$this->fields[$name]->uniquize();

			foreach ($this->fields[$name] as $validator) {
				if (!$this->validators[$validator]) {
					$this->validators[$validator] = null;
				}
			}
		}
	}

	private function parseValidators (Tuple $config) {
		$this->validators->setParameters($config);
		foreach ($this->validators as $name => $values) {
			if (!$values) {
				$message = new StringFormat('バリデータ "%s" が未定義です。');
				$message[] = $name;
				throw new ConfigException($message);
			}
			$this->validators[$name] = Tuple::create($values);
		}
	}
}

