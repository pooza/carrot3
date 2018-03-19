<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage string.translate
 */

namespace Carrot3;

/**
 * 辞書ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DictionaryFile extends CSVFile implements Dictionary {

	/**
	 * @access public
	 * @param string $path パス
	 * @param string $class レンダラークラス名
	 */
	public function __construct ($path, $class = 'HeaderCSVData') {
		parent::__construct($path, $class);
		if ($this->isExists() && !$this->getSerialized()) {
			$this->serialize();
		}
	}

	/**
	 * 辞書の内容を返す
	 *
	 * @access public
	 * @return Tuple 辞書の内容
	 */
	public function getWords () {
		return Tuple::create($this->getSerialized());
	}

	/**
	 * 翻訳して返す
	 *
	 * @access public
	 * @param string $label ラベル
	 * @param string $lang 言語
	 * @return string 翻訳された文字列
	 */
	public function translate ($label, ?string $lang) {
		return $this->getWords()[$label . '_' . $lang];
	}

	/**
	 * 辞書の名前を返す
	 *
	 * @access public
	 * @return string 辞書の名前
	 */
	public function getDictionaryName () {
		return Utils::getClass($this) . '.' . $this->getBaseName();
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		(new SerializeHandler)->setAttribute(
			$this,
			(clone $this->getRenderer()->getRecords())->flatten()
		);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('辞書ファイル "%s"', $this->getShortPath());
	}
}
