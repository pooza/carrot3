<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.json
 */

namespace Carrot3;

/**
 * API結果文書用 JSONレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ResultJSONRenderer extends JSONRenderer {
	private $params;

	/**
	 * @access public
	 */
	public function __construct () {
		$this->params = Tuple::create();
		$this->params['status'] = 200;
		$this->result = Tuple::create();
	}

	/**
	 * パラメータ配列を返す
	 *
	 * @access public
	 */
	public function getParameters () {
		return $this->params;
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents () {
		$contents = $this->result->decode();
		$contents['api'] = $this->params->decode();
		return $this->getSerializer()->encode($contents);
	}

	/**
	 * 出力内容を設定
	 *
	 * @param string $contents 出力内容
	 * @access public
	 */
	public function setContents ($contents) {
		if (!is_iterable($contents)) {
			throw new Exception(Utils::getClass($this) . 'は、配列でない結果文書を返せません。');
		}
		$this->result = Tuple::create($contents);
	}
}
