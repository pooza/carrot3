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
class DictionaryFile extends ConfigFile implements Dictionary, Serializable {
	use SerializableFile;

	/**
	 * @access public
	 * @param string $path パス
	 */
	public function __construct ($path) {
		parent::__construct($path);
		if ($this->isExists() && !$this->isSerialized()) {
			$this->serialize();
		}
	}

	/**
	 * 辞書の内容を返す
	 *
	 * @access public
	 * @return Tuple 辞書の内容
	 */
	public function getWords ():Tuple {
		return Tuple::create($this->getSerialized()['result']);
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		$values = Tuple::create($this->getAttributes());
		$values['result'] = $this->getResult();
		(new SerializeHandler)->setAttribute($this, $values);
	}

	/**
	 * 翻訳して返す
	 *
	 * @access public
	 * @param string $label ラベル
	 * @param string $lang 言語
	 * @return string 翻訳された文字列
	 */
	public function translate (string $label, ?string $lang):?string {
		return $this->getWords()[$label][$lang];
	}

	/**
	 * 辞書の名前を返す
	 *
	 * @access public
	 * @return string 辞書の名前
	 */
	public function getDictionaryName ():string {
		return Utils::getClass($this) . '.' . $this->getBaseName();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('辞書ファイル "%s"', $this->getShortPath());
	}
}
