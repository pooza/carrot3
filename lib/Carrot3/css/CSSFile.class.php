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
class CSSFile extends File implements Serializable {
	use SerializableFile;

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return bool バイナリファイルならTrue
	 */
	public function isBinary ():bool {
		return false;
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType ():string {
		return MIMEType::getType('css');
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
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		$values = Tuple::create($this->getAttributes());
		require_once BS_LIB_DIR . '/Minify/CSS/Compressor.php';
		$values['minified'] = \Minify_CSS_Compressor::process($this->getContents());
		(new SerializeHandler)->setAttribute($this, $values);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('CSSファイル "%s"', $this->getShortPath());
	}
}
