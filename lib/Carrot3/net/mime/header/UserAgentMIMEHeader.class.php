<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * User-Agentヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class UserAgentMIMEHeader extends MIMEHeader {
	protected $name = 'User-Agent';
	private $useragent;

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return UserAgent 実体
	 */
	public function getEntity () {
		return $this->useragent;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof UserAgent) {
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
			$this->useragent = UserAgent::create($this->contents);
		} catch (NetException $e) {
		}
	}
}

