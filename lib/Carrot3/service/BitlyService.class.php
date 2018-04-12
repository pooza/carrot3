<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service
 */

namespace Carrot3;

/**
 * bit.lyクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class BitlyService extends CurlHTTP implements URLShorter {
	const DEFAULT_HOST = 'api-ssl.bitly.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param int $port ポート
	 */
	public function __construct (Host $host = null, int $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
			$port = NetworkService::getPort('https');
		}
		parent::__construct($host, $port);
	}

	/**
	 * パスからリクエストURLを生成して返す
	 *
	 * @access public
	 * @param string $href パス
	 * @return HTTPURL リクエストURL
	 */
	public function createRequestURL ($href) {
		$url = parent::createRequestURL($href);
		$url->setParameter('access_token', BS_SERVICE_BITLY_TOKEN);
		return $url;
	}

	/**
	 * 短縮URLを返す
	 *
	 * @access public
	 * @param HTTPRedirector $url 対象URL
	 * @return HTTPURL 短縮URL
	 */
	public function getShortURL (HTTPRedirector $url) {
		$request = $this->createRequestURL('/v3/shorten');
		$request->setParameter('longUrl', $url->getContents());
		$response = $this->sendGET($request->getFullPath());

		return URL::create(Tuple::create(
			(new JSONSerializer)->decode($response->getRenderer()->getContents())
		)['data']['url']);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('bit.ly "%s"', $this->getName());
	}
}
