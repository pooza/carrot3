<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google Hubクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleHubService extends PublisherService {
	const DEFAULT_HOST = 'pubsubhubbub.appspot.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param integer $port ポート
	 */
	public function __construct (Host $host = null, $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
			$port = NetworkService::getPort('https');
		}
		parent::__construct($host, $port);
		$this->setURL(URL::create('https://' . $host->getName()));
	}
}
