<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml
 */

namespace Carrot3;

/**
 * 整形式XML文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class XMLDocument extends XMLElement implements TextRenderer {
	private $dirty = false;
	private $error;

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType ():string {
		return MIMEType::getType('xml');
	}

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding ():string {
		return 'utf-8';
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize ():int {
		return strlen($this->getContents());
	}

	/**
	 * ダーティモードか？
	 *
	 * @access public
	 * @return bool ダーティモードならTrue
	 */
	public function isDirty ():bool {
		return $this->dirty;
	}

	/**
	 * ダーティモードを設定
	 *
	 * libxml2がエラーを起こすXML文書を無理やり処理する。
	 *
	 * @access public
	 * @param bool $mode ダーティモード
	 */
	public function setDirty (bool $mode) {
		$this->dirty = $mode;
		$this->attributes->clear();
		$this->elements->clear();
		$this->setBody();
	}

	/**
	 * 内容をXMLで返す
	 *
	 * @access public
	 * @return string XML文書
	 */
	public function getContents ():string {
		$contents = '<?xml version="1.0" encoding="utf-8" ?>' . parent::getContents();
		if ($this->isDirty()) {
			return $contents;
		} else {
			$xml = new \DOMDocument('1.0', 'utf-8');
			$xml->loadXML($contents);
			$xml->formatOutput = true;
			$xml->normalizeDocument();
			return $xml->saveXML();
		}
	}

	/**
	 * 妥当な要素か？
	 *
	 * @access public
	 * @return bool 妥当な要素ならTrue
	 */
	public function validate ():bool {
		if (!parent::getContents()) {
			$this->error = '妥当なXML文書ではありません。';
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
	public function getError ():?string {
		return $this->error;
	}

	/**
	 * コメントを削除
	 *
	 * @access public
	 * @param string $value 対象文字列
	 * @return string 変換後の文字列
	 * @static
	 */
	static public function stripComment ($value) {
		return mb_ereg_replace('<!--.*?-->', null, $value);
	}
}
