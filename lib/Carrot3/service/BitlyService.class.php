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
	const DEFAULT_HOST = 'api.bit.ly';

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
		$url->setParameter('version', BS_SERVICE_BITLY_VERSION);
		$url->setParameter('login', BS_SERVICE_BITLY_LOGIN);
		$url->setParameter('apiKey', BS_SERVICE_BITLY_API_KEY);
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
		$request = $this->createRequestURL('shorten');
		$request->setParameter('longUrl', $url->getContents());
		$response = $this->sendGET($request->getFullPath());

		$json = new JSONSerializer;
		$result = $json->decode($response->getRenderer()->getContents());
		$result = Tuple::create($result['results']);
		$result = Tuple::create($result->getIterator()->getFirst());
		return URL::create($result['shortUrl']);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('bit.ly "%s"', $this->getName());
	}
}

