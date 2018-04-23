<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.query
 */

namespace Carrot3;

/**
 * 抽出条件の集合
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Criteria extends Tuple {
	private $glue = 'AND';
	private $db;

	/**
	 * @access public
	 * @param mixed $params 要素の配列
	 */
	public function __construct ($params = []) {
		parent::__construct($params);
	}

	/**
	 * 接続子を返す
	 *
	 * @access public
	 * @return string 接続子
	 */
	public function getGlue () {
		return $this->glue;
	}

	/**
	 * 接続子を設定
	 *
	 * @access public
	 * @param string $glue 接続子
	 */
	public function setGlue ($glue) {
		$this->glue = StringUtils::toUpper($glue);
	}

	/**
	 * 対象データベースを返す
	 *
	 * @access public
	 * @return Database 対象データベース
	 */
	public function getDatabase ():Database {
		if (!$this->db) {
			$this->db = Database::getInstance();
		}
		return $this->db;
	}

	/**
	 * 対象データベースを設定
	 *
	 * @access public
	 * @param Database $db 対象データベース
	 */
	public function setDatabase (Database $db) {
		$this->db = $db;
	}

	/**
	 * 条件を登録
	 *
	 * @access public
	 * @param string $key フィールド名
	 * @param mixed $value 値又はその配列
	 * @param string $operator 演算子
	 */
	public function register ($key, $value, $operator = '=') {
		$key = trim(StringUtils::toLower($key));
		$operator = trim(StringUtils::toUpper($operator));

		switch ($operator) {
			case 'BETWEEN':
				$values = Tuple::create($value);
				if ($values->count() != 2) {
					throw new \InvalidArgumentException('BETWEEN演算子に与える引数は2個です。');
				}
				$this[] = $key . ' BETWEEN ' . $this->quote($values)->join(' AND ');
				break;
			case 'NOT IN':
				$values = Tuple::create($value);
				if ($values->count()) {
					$values->uniquize();
					$this[] = $key . ' NOT IN (' . $this->quote($values)->join(',') . ')';
				}
				break;
			default:
				if ($value === null) {
					$this[] = $key . ' IS NULL';
				} else if ($value instanceof Tuple) {
					$values = $value;
					if ($values->count()) {
						$values->uniquize();
						$this[] = $key . ' IN (' . $this->quote($values)->join(',') . ')';
					} else {
						$this[] = $key . ' IS NULL';
					}
				} else if ($value instanceof Record) {
					$this[] = $key . ' ' . $operator . ' ' . $this->quote($value->getID());
				} else {
					$this[] = $key . ' ' . $operator . ' ' . $this->quote($value);
				}
				break;
		}
	}

	/**
	 * 値をクォート
	 *
	 * @access public
	 * @param mixed $value 値又はその配列
	 * @param string $operator 演算子
	 * @return mixed クォートされた値
	 */
	public function quote ($value) {
		if (is_iterable($value)) {
			$ids = Tuple::create();
			foreach (Tuple::create($value) as $item) {
				$ids[] = $this->getDatabase()->quote($item);
			}
			return $ids;
		} else {
			return $this->getDatabase()->quote($value);
		}
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents ():string {
		$contents = Tuple::create();
		foreach ($this as $criteria) {
			if ($criteria instanceof Criteria) {
				$contents[] = '(' . $criteria->getContents() . ')';
			} else {
				$contents[] = '(' . $criteria . ')';
			}
		}
		return $contents->join(' ' . $this->getGlue() . ' ');
	}
}
