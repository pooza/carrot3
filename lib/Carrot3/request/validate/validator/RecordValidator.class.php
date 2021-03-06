<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request.validate.validator
 */

namespace Carrot3;

/**
 * レコードバリデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RecordValidator extends Validator {
	protected $table;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ配列
	 * @return bool
	 */
	public function initialize (?iterable $params = []):bool {
		$this['table'] = null;
		$this['class'] = null;
		$this['field'] = 'id';
		$this['exist'] = true;
		$this['update'] = false;
		$this['exist_error'] = '登録されていません。';
		$this['duplicate_error'] = '既に登録されています。';
		$this['valid_values'] = [];
		$this['criteria'] = [];
		return parent::initialize($params);
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param mixed $value バリデート対象（レコードのID、又はその配列）
	 * @return bool 妥当な値ならばTrue
	 */
	public function execute ($value) {
		$ids = Tuple::create($value);
		$ids->trim();
		foreach ($ids as $id) {
			if ($this->isExists($id)) {
				if (!$this['exist']) {
					$this->error = $this['duplicate_error'];
					return false;
				} else if ($this['valid_values'] && !$this->validateValues($id)) {
					return false;
				}
			} else {
				if ($this['exist']) {
					$this->error = $this['exist_error'];
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * レコードが存在するか
	 *
	 * @access protected
	 * @param mixed $id レコードのID
	 * @return bool 存在するならばTrue
	 */
	protected function isExists ($id):bool {
		if ($recordFound = $this->getRecord($id)) {
			if ($this['update']) {
				if ($recordModule = $this->controller->getModule()->getRecord()) {
					return ($recordModule->getID() != $recordFound->getID());
				} else {
					return false;
				}
			} else {
				return true;
			}
		}
		return false;
	}

	/**
	 * 妥当な値か
	 *
	 * @access protected
	 * @param mixed $id レコードのID
	 * @return bool 妥当な値ならばTrue
	 */
	protected function validateValues ($id) {
		$record = $this->getRecord($id);
		foreach ($this['valid_values'] as $field => $value) {
			$values = Tuple::create($value);
			if (!$values->isContain($record[$field])) {
				$message = new StringFormat('%sが正しくありません。');
				$message[] = $this->translator->translate($field);
				$this->error = $message->getContents();
				return false;
			}
		}
		return true;
	}

	/**
	 * クラスのstaticメソッドを実行
	 *
	 * @access protected
	 * @param string $function メソッド名
	 * @return bool メソッドの戻り値
	 */
	protected function executeModuleFunction ($function) {
		$value = $this->controller->getModule()->$function();
		if ($value instanceof Record) {
			$value = $value->getID();
		}
		return $value;
	}

	/**
	 * 対象レコードを取得
	 *
	 * @access protected
	 * @param mixed $id レコードのID
	 * @return Record 対象レコード
	 */
	protected function getRecord ($id):?Record {
		try {
			$values = [$this['field'] => $id];
			foreach (Tuple::create($this['criteria']) as $field => $value) {
				if (isset($value['function'])) {
					$value = $this->executeModuleFunction($value['function']);
				}
				$values[$field] = $value;
			}
			return $this->getTable()->getRecord($values);
		} catch (\Throwable $e) {
			return null;
		}
	}

	/**
	 * 対象テーブルを取得
	 *
	 * @access protected
	 * @return TableHandler 対象テーブル
	 */
	protected function getTable () {
		if (!$this->table) {
			if (StringUtils::isBlank($class = $this['class'])) {
				$class = $this['table'];
			}
			$this->table = TableHandler::create($class);
		}
		return $this->table;
	}
}
