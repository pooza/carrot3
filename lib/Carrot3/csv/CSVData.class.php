<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage csv
 */

namespace Carrot3;

/**
 * CSVデータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @link http://project-p.jp/halt/kinowiki/php/Tips/csv 参考
 * @link http://www.din.or.jp/~ohzaki/perl.htm#CSV2Values 参考
 */
class CSVData implements TextRenderer, \IteratorAggregate, \Countable {
	protected $contents;
	protected $records;
	protected $encoding = 'sjis-win';
	protected $recordSeparator = "\r\n";
	protected $fieldSeparator = ',';
	protected $error;
	private $commaSearchPattern;
	const COMMA_TAG = '_COMMA_';

	/**
	 * @access public
	 * @param string $contents
	 */
	public function __construct ($contents = null) {
		$this->records = Tuple::create();
		$this->setContents($contents);
	}

	/**
	 * 行をセットして、レコード配列を生成
	 *
	 * @access public
	 * @param Tuple $lines
	 */
	public function setLines (Tuple $lines) {
		$lines = StringUtils::convertEncoding($lines);
		$this->records = Tuple::create();
		foreach ($lines as $line) {
			if (isset($record) && $record) {
				$record .= "\n" . $line;
			} else {
				$record = $line;
			}

			if (!$this->isCompleteRecord($record)) {
				continue;
			}

			$record = $this->escapeCommaValue($record);
			$record = StringUtils::explode($this->getFieldSeparator(), $record);
			$record = $this->unescapeCommaValue($record);
			$this->addRecord($record);
			$record = null;
		}
	}

	private function isCompleteRecord ($record) {
		return (StringUtils::eregMatchAll('"', $record)->count() % 2) == 0;
	}

	private function escapeCommaValue ($record) {
		while (mb_ereg($this->getCommaSearchPattern(), $record, $matches)) {
			$record = str_replace(
				$matches[1],
				str_replace($this->getFieldSeparator(), self::COMMA_TAG, $matches[1]),
				$record
			);
		}
		return $record;
	}

	private function unescapeCommaValue (Tuple $record) {
		foreach ($record as $key => $value) {
			$record[$key] = str_replace(self::COMMA_TAG, $this->getFieldSeparator(), $value);
		}
		return $record;
	}

	private function getCommaSearchPattern () {
		if (!$this->commaSearchPattern) {
			$pattern = '_COMMA_"(([^"_COMMA_]*_COMMA_)+)([^"_COMMA_]*)?"';
			$pattern = str_replace(self::COMMA_TAG, $this->getFieldSeparator(), $pattern);
			$this->commaSearchPattern = $pattern;
		}
		return $this->commaSearchPattern;
	}

	/**
	 * レコードを追加
	 *
	 * @access public
	 * @param Tuple $record
	 */
	public function addRecord (Tuple $record) {
		if (StringUtils::isBlank($record[0])) {
			return;
		}
		$this->records[] = $this->trimRecord($record);
		$this->contents = null;
	}

	/**
	 * レコードをトリミング
	 *
	 * @access protected
	 * @param Tuple $record レコード
	 * @return Tuple トリミングされたレコード
	 */
	protected function trimRecord (Tuple $record) {
		foreach ($record as $key => $field) {
			$field = rtrim($field);
			$field = mb_ereg_replace('"(.*)"', '\\1', $field, 'm');
			$field = str_replace('""', '"', $field);
			$record[$key] = $field;
		}
		return $record;
	}

	/**
	 * 全てのレコードを返す
	 *
	 * @access public
	 * @return array 全てのレコード
	 */
	public function getRecords () {
		if (!$this->records->count() && $this->contents) {
			$this->setLines(StringUtils::explode($this->getRecordSeparator(), $this->contents));
		}
		return $this->records;
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string CSVデータの内容
	 */
	public function getContents () {
		if (!$this->contents) {
			$contents = Tuple::create();
			foreach ($this->getRecords() as $key => $record) {
				foreach ($record as $key => $field) {
					$field = '"' . str_replace('"', '""', $field) . '"';
					$record[$key] = $field;
				}
				$contents[] = $record->join($this->getFieldSeparator());
			}
			$contents = $contents->join($this->getRecordSeparator());
			$contents = StringUtils::convertEncoding($contents, $this->getEncoding());
			$contents = StringUtils::convertLineSeparator($contents, $this->getRecordSeparator());
			$this->contents = $contents;
		}
		return $this->contents;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param string $contents CSVデータの内容
	 */
	public function setContents ($contents) {
		$contents = StringUtils::convertLineSeparator($contents, $this->getRecordSeparator());
		$contents = StringUtils::convertEncoding($contents, $this->getEncoding());
		$this->contents = $contents;
		$this->records = Tuple::create();
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize () {
		return strlen($this->getContents());
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType('csv');
	}

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding () {
		return $this->encoding;
	}

	/**
	 * エンコードを設定
	 *
	 * @access public
	 * @param string $encoding PHPのエンコード名
	 */
	public function setEncoding ($encoding) {
		if (StringUtils::isBlank(mb_preferred_mime_name($encoding))) {
			throw new ViewException('利用できないエンコード名です。');
		}
		$this->encoding = $encoding;
	}

	/**
	 * レコード区切りを返す
	 *
	 * @access public
	 * @return string レコード区切り
	 */
	public function getRecordSeparator () {
		return $this->recordSeparator;
	}

	/**
	 * レコード区切りを設定
	 *
	 * @access public
	 * @param string $recordSeparator レコード区切り
	 */
	public function setRecordSeparator ($separator) {
		$this->recordSeparator = $separator;
	}

	/**
	 * フィールド区切りを返す
	 *
	 * @access public
	 * @return string フィールド区切り
	 */
	public function getFieldSeparator () {
		return $this->fieldSeparator;
	}

	/**
	 * フィールド区切りを設定
	 *
	 * @access public
	 * @param string $fieldSeparator フィールド区切り
	 */
	public function setFieldSeparator ($separator) {
		$this->fieldSeparator = $separator;
	}

	/**
	 * @access public
	 * @return int レコード数
	 */
	public function count () {
		return $this->records->count();
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		if (!($this->getRecords() instanceof Tuple)) {
			$this->error = 'データ配列が正しくありません。';
			return false;
		}
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return $this->error;
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return new Iterator($this->getRecords());
	}
}
