<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate
 */

namespace Carrot3;

/**
 * バリデートマネージャ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ValidateManager implements \IteratorAggregate {
	use Singleton, BasicObject;
	private $fields;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->fields = Tuple::create();
	}

	/**
	 * 実行
	 *
	 * @access public
	 */
	public function execute () {
		foreach ($this as $field => $validators) {
			foreach ($validators as $validator) {
				if (!EmptyValidator::isEmpty($this->request[$field])
					|| ($validator['fields'])
					|| ($validator instanceof EmptyValidator)) {

					if (!$validator->execute($this->request[$field])) {
						$this->request->setError($field, $validator->getError());
						break;
					}
				}
			}
		}
		return !$this->request->hasErrors();
	}

	/**
	 * フィールドにバリデータを登録
	 *
	 * @access public
	 * @param string $name フィールド名
	 * @param mixed $validator バリデータ、又はバリデータ名
	 */
	public function register (string $name, $validator) {
		if (!$this->fields[$name]) {
			$this->fields[$name] = Tuple::create();
		}
		if (!($validator instanceof Validator)) {
			if (!$validator = $this->createValidator($validator)) {
				$message= new StringFormat('バリデータ "%s" が見つかりません。');
				$message[] = $name;
				throw new ValidateException($message);
			}
		}
		$this->fields[$name][$validator->getName()] = $validator;
	}

	/**
	 * フィールド名を返す
	 *
	 * @access public
	 * @return Tuple フィールド名
	 */
	public function getFieldNames () {
		return $this->fields->getKeys();
	}

	/**
	 * フィールド値を返す
	 *
	 * @access public
	 * @return Tuple フィールド値
	 */
	public function getFieldValues () {
		$values = Tuple::create();
		foreach ($this->getFieldNames() as $name) {
			$values[$name] = $this->request[$name];
		}
		return $values;
	}

	/**
	 * バリデータを生成して返す
	 *
	 * @access public
	 * @param string $name バリデータ名
	 * @return Validator バリデータ
	 */
	public function createValidator (string $name) {
		if ($entry = ConfigCompiler::parseFiles('validator')[$name]) {
			$entry = Tuple::create($entry);
			$class = $this->loader->getClass($entry['class']);
			return new $class($entry['params']);
		}
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return $this->fields->getIterator();
	}
}
