<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table_profile
 */

namespace Carrot3;

/**
 * テーブルのプロフィール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class TableProfile implements Assignable, Serializable {
	use BasicObject, SerializableMethods;
	protected $database;
	protected $fields;
	protected $constraints;
	protected $digest;
	private $name;

	/**
	 * @access public
	 * @param string $table テーブル名
	 */
	public function __construct ($table, Database $database = null) {
		if (!$database) {
			$database = Database::getInstance();
		}
		$this->database = $database;
		$this->name = $table;

		if (!$this->isExists()) {
			throw new DatabaseException($this . 'が取得できません。');
		}

		if (!$this->getSerialized()) {
			$this->serialize();
		}
		$this->fields = Tuple::create($this->getSerialized()['fields']);
		$this->constraints = Tuple::create($this->getSerialized()['constraints']);
	}

	/**
	 * テーブル名を返す
	 *
	 * @access public
	 * @return string テーブル名
	 */
	public function getName () {
		return $this->name;
	}

	/**
	 * データベースを返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function getDatabase () {
		return $this->database;
	}

	/**
	 * テーブルは存在するか？
	 *
	 * @access public
	 * @return boolean 存在するならTrue
	 */
	public function isExists () {
		return $this->getDatabase()->getTableNames()->isContain($this->getName());
	}

	/**
	 * テーブルのフィールドリストを配列で返す
	 *
	 * @access public
	 * @return Tuple フィールドのリスト
	 * @abstract
	 */
	abstract public function getFields ();

	/**
	 * テーブルの制約リストを配列で返す
	 *
	 * @access public
	 * @return Tuple 制約のリスト
	 * @abstract
	 */
	abstract public function getConstraints ();

	/**
	 * テーブルクラスを返す
	 *
	 * @access protected
	 * @return string テーブルクラス
	 */
	protected function getTableClass () {
		return $this->loader->getClass(Utils::getClass($this));
	}

	/**
	 * レコードクラスを返す
	 *
	 * @access protected
	 * @return string レコードクラス
	 */
	protected function getRecordClass () {
		return $this->loader->getClass($this->getName());
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest () {
		if (!$this->digest) {
			$this->digest = Crypt::digest([
				Utils::getClass($this),
				$this->getName(),
			]);
		}
		return $this->digest;
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		$this->controller->setAttribute($this, [
			'fields' => $this->getFields(),
			'constraints' => $this->getConstraints(),
		]);
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		$values = [
			'name' => $this->getName(),
			'name_ja' => TranslateManager::getInstance()->execute($this->getName(), 'ja'),
			'class' => $this->getTableClass(),
			'record_class' => $this->getRecordClass(),
			'constraints' => $this->getConstraints(),
		];

		$pattern = '^(' . $this->getDatabase()->getTableNames()->join('|') . ')_id$';
		foreach ($this->getFields() as $field) {
			if (isset($field['is_nullable'])) {
				$field['is_nullable'] = ($field['is_nullable'] == 'YES');
			}
			if (mb_ereg($pattern, $field['column_name'], $matches)) {
				$field['extrenal_table'] = $matches[1];
			}
			$values['fields'][] = $field;
		}

		return $values;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('テーブルプロフィール "%s"', $this->getName());
	}
}
