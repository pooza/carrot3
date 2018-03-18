<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.table
 */

namespace Carrot3;

/**
 * シリアライズ可能なデータベーステーブル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
trait SerializableTable {

	/**
	 * @access public
	 * @param mixed $criteria 抽出条件
	 * @param mixed $order ソート順
	 */
	public function __construct ($criteria = null, $order = null) {
		if (!$this->getSerialized()) {
			$this->serialize();
		}
		$this->setExecuted(true);
	}

	/**
	 * 出力フィールド文字列を設定
	 *
	 * @access public
	 * @param mixed $fields 配列または文字列による出力フィールド
	 */
	public function setFields ($fields) {
		if ($fields) {
			throw new DatabaseException('変更できません。');
		}
	}

	/**
	 * 名前からIDを返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return int ID
	 */
	public function getID (string $name) {
		foreach ($this as $record) {
			if ($record->getAttribute('name') == $name) {
				return $record->getID();
			}
		}
	}

	/**
	 * 結果を返す
	 *
	 * @access public
	 * @return array 結果の配列
	 */
	public function getResult () {
		return $this->getSerialized();
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest () {
		return Crypt::digest(Utils::getClass($this));
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		(new SerializeHandler)->setAttribute($this, parent::getResult());
	}
}
