<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage css
 */

namespace Carrot3;

/**
 * CSSファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CSSFile extends File {

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return boolean バイナリファイルならTrue
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
		return MIMEType::getType('css');
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
		require_once BS_LIB_DIR . '/Minify/CSS/Compressor.php';
		$contents = \Minify_CSS_Compressor::process($this->getContents());
		(new SerializeHandler)->setAttribute($this, $contents);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('CSSファイル "%s"', $this->getShortPath());
	}
}
