<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty
 */

namespace Carrot3;

/**
 * テンプレートファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TemplateFile extends File {
	private $engine;
	private $body;

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
	 * テンプレートエンジンを設定
	 *
	 * @access public
	 * @param Smarty $engine テンプレートエンジン
	 */
	public function setEngine (Smarty $engine) {
		$this->engine = $engine;
	}

	/**
	 * コンパイル
	 *
	 * @access public
	 * @return string コンパイル結果
	 */
	public function compile ():string {
		if (!$this->body) {
			$this->body = $this->engine->fetch($this->getPath());
		}
		return $this->body;
	}
}
