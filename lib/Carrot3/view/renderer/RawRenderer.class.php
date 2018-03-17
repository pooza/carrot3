<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer
 */

namespace Carrot3;

/**
 * 汎用レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RawRenderer implements Renderer {
	private $contents;
	private $type = MIMEType::DEFAULT_TYPE;

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents () {
		return $this->contents;
	}

	/**
	 * 出力内容を設定
	 *
	 * @param string $contents 出力内容
	 * @access public
	 */
	public function setContents ($contents) {
		$this->contents = $contents;
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
		return $this->type;
	}

	/**
	 * メディアタイプを設定
	 *
	 * @access public
	 * @param string $type メディアタイプ又は拡張子
	 */
	public function setType ($type) {
		if (!StringUtils::isBlank($suggested = MIMEType::getType($type, null))) {
			$type = $suggested;
		}
		$this->type = $type;
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return null;
	}
}
