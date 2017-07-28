<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http
 */

namespace Carrot3;

/**
 * HTTP例外
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HTTPException extends NetException {
	private $response;

	/**
	 * 例外を含んだレスポンスを返す
	 *
	 * @return HTTPResponse レスポンス
	 */
	public function getResponse () {
		return $this->response;
	}

	/**
	 * レスポンスを格納
	 *
	 * @param HTTPResponse $response レスポンス
	 */
	public function setResponse (HTTPResponse $response) {
		$this->response = $response;
	}
}

