<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * YouTubeクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class YouTubeService extends CurlHTTP {
	private $useragent;
	const DEFAULT_HOST = 'www.youtube.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param integer $port ポート
	 */
	public function __construct (Host $host = null, $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
		}
		parent::__construct($host, $port);
		$this->useragent = $this->request->getUserAgent();
	}

	/**
	 * 対象UserAgentを設定
	 *
	 * @access public
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function setUserAgent (UserAgent $useragent) {
		$this->useragent = $useragent;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('YouTube "%s"', $this->getName());
	}
}
