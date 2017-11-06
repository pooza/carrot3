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
	private $compiled;
	private $body;

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
	public function compile () {
		if (!$this->body) {
			$this->body = $this->engine->fetch($this->getPath());
		}
		return $this->body;
	}

	/**
	 * コンパイル済みファイルを返す
	 *
	 * @access public
	 * @return File コンパイル済みファイル
	 */
	public function getCompiled () {
		if (!$this->compiled) {
			return new File($this->engine->_get_compile_path($this->getPath()));
		}
		return $this->compiled;
	}
}
