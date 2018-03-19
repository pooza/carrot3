<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * Hostヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HostMIMEHeader extends MIMEHeader {
	protected $name = 'Host';
	private $host;
	private $port;

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return Host 実体
	 */
	public function getEntity () {
		return $this->host;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof Host) {
			$contents = $contents->getName();
		}
		parent::setContents($contents);
	}

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		parent::parse();
		try {
			$parts = StringUtils::explode(':', $this->contents);
			$this->host = new Host($parts[0]);
			$this->port = $parts[1];
		} catch (NetException $e) {
			// ログのみ
		}
	}
}
