<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage js
 */

namespace Carrot3;

/**
 * JavaScriptファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JavaScriptFile extends File {

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return bool バイナリファイルならTrue
	 */
	public function isBinary () {
		return false;
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType('js');
	}

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding () {
		return 'utf-8';
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		require_once BS_LIB_DIR . '/jsmin.php';
		(new SerializeHandler)->setAttribute($this, \JSMin::minify($this->getContents()));
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('JavaScriptファイル "%s"', $this->getShortPath());
	}
}
