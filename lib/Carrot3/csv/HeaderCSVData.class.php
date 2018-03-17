<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage csv
 */

namespace Carrot3;

/**
 * ヘッダ付きCSVデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HeaderCSVData extends CSVData {
	protected $fields;
	protected $hasRowID = true;

	/**
	 * @access public
	 * @param string $contents
	 */
	public function __construct ($contents = null) {
		$this->fields = Tuple::create();
		parent::__construct($contents);
	}

	/**
	 * 見出しを返す
	 *
	 * @access public
	 * @return Tuple 見出し
	 */
	public function getFieldNames () {
		return $this->fields;
	}

	/**
	 * 見出しを設定
	 *
	 * @access public
	 * @param Tuple $fields 見出し
	 */
	public function setFieldNames (Tuple $fields) {
		$fields = StringUtils::convertEncoding($fields);
		$fields = $this->trimRecord($fields);
		$this->fields = $fields;
	}

	/**
	 * 見出しをひとつ返す
	 *
	 * @access public
	 * @param int $index 序数
	 * @return string 見出し
	 */
	public function getFieldName (int $index) {
		return $this->fields[$index];
	}

	/**
	 * 見出し行を返す
	 *
	 * @access public
	 * @return string 見出し行
	 */
	public function getHeader () {
		return $this->getFieldNames()->join($this->getFieldSeparator())
			. $this->getRecordSeparator();
	}

	/**
	 * 行IDを持つか
	 *
	 * @access public
	 * @return bool 行IDを持つならTrue
	 */
	public function hasRowID () {
		return $this->hasRowID;
	}

	/**
	 * 行IDを持つかを設定
	 *
	 * @access public
	 * @param bool $flag 行IDを持つならTrue
	 */
	public function setHasRowID (bool $flag) {
		$this->hasRowID = $flag;
	}

	/**
	 * 行をセットして、レコード配列を生成
	 *
	 * @access public
	 * @param Tuple $lines
	 */
	public function setLines (Tuple $lines) {
		$this->setFieldNames(StringUtils::explode($this->getFieldSeparator(), $lines->shift()));
		parent::setLines($lines);
	}

	/**
	 * レコードを追加
	 *
	 * @access public
	 * @param Tuple $record
	 */
	public function addRecord (Tuple $record) {
		if (StringUtils::isBlank($record[$this->getFieldName(0)])) {
			$newRecord = Tuple::create();
			for ($i = 0 ; $i < $this->getFieldNames()->count() ; $i ++) {
				$newRecord[$this->getFieldName($i)] = $record[$i];
			}
			$record = $newRecord;
		}

		if ($this->hasRowID()) {
			if (StringUtils::isBlank($record[$this->getFieldName(0)])) {
				return;
			}
			$this->records[$record[$this->getFieldName(0)]] = $this->trimRecord($record);
		} else {
			$this->records[] = $this->trimRecord($record);
		}
		$this->contents = null;
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string CSVデータの内容
	 */
	public function getContents () {
		return $this->getHeader() . parent::getContents();
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		if (!$this->getHeader()) {
			$this->error = '見出し行が正しくありません。';
			return false;
		}
		return parent::validate();
	}
}
