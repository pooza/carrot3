<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google URL Shortnerクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleURLShortnerService extends CurlHTTP implements URLShorter {
	const DEFAULT_HOST = 'www.googleapis.com';

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
		$url->setParameter('key', BS_SERVICE_GOOGLE_URL_SHORTENER_API_KEY);
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
		$json = new JSONRenderer;
		$json->setContents([
			'longUrl' => $url->getURL()->getContents(),
		]);
		$url = $this->createRequestURL('/urlshortener/v1/url');
		$response = $this->sendPOST($url->getFullPath(), $json);

		$json = new JSONSerializer;
		$result = $json->decode($response->getRenderer()->getContents());
		return URL::create($result['id']);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Google URL Shortner "%s"', $this->getName());
	}
}
