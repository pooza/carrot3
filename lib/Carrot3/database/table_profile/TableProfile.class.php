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
	use BasicObject, SerializableObject, KeyGenerator;
	protected $database;
	protected $fields;
	protected $constraints;
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

		if (!$this->isSerialized()) {
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
	public function getName ():string {
		return $this->name;
	}

	/**
	 * データベースを返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function getDatabase ():Database {
		return $this->database;
	}

	/**
	 * テーブルは存在するか？
	 *
	 * @access public
	 * @return bool 存在するならTrue
	 */
	public function isExists ():bool {
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
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->createKey([
			$this->getDatabase()['dsn'],
			$this->getName(),
		]);
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		$values = Tuple::create([
			'name' => $this->getName(),
			'name_ja' => $this->translator->translate($this->getName(), 'ja'),
			'fields' => [],
			'constraints' => $this->getConstraints(),
		]);
		$pattern = '^(' . $this->getDatabase()->getTableNames()->join('|') . ')_id$';
		foreach ($this->getFields() as $field) {
			if (isset($field['is_nullable'])) {
				$field['is_nullable'] = ($field['is_nullable'] == 'YES');
			}
			if (mb_ereg($pattern, $field['column_name'], $matches)) {
				$field['extrenal_table'] = $matches[1];
			}
			$values['fields'][$field['column_name']] = $field;
		}
		(new SerializeHandler)->setAttribute($this, $values);
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getSerialized();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('テーブルプロフィール "%s"', $this->getName());
	}
}
