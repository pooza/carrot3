<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google Maps Geocodingクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleMapsGeocodingService extends CurlHTTP {
	private $table;
	const DEFAULT_HOST = 'maps.googleapis.com';

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
	}

	/**
	 * ジオコードを返す
	 *
	 * @access public
	 * @param string $address 住所等
	 * @return Geocode ジオコード
	 */
	public function getGeocode ($address) {
		$key = Crypt::digest([Utils::getClass($this), $address]);
		if (!$geocode = $this->controller->getAttribute($key)) {
			$pattern = '^lat=([.[:digit:]]+),lng=([.[:digit:]]+)+$';
			if (mb_ereg($pattern, $address, $matches)) {
				$geocode = ['lat' => $matches[1], 'lng' => $matches[2]];
			} else {
				$geocode = $this->query($address);
			}
			$this->controller->setAttribute($key, $geocode);
		}
		return new Geocode($geocode);
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
		$url->setParameter('key', BS_SERVICE_GOOGLE_MAPS_GEOCODING_API_KEY);
		return $url;
	}

	protected function query ($address) {
		$url = $this->createRequestURL('/maps/api/geocode/json');
		$url->setParameter('address', $address);
		$response = $this->sendGET($url->getFullPath());

		$serializer = new JSONSerializer;
		$result = $serializer->decode(base64_decode($response->getBody()));
		if (isset($result['results'][0]['geometry']['location'])) {
			return Tuple::create($result['results'][0]['geometry']['location']);
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Google Maps Geocoding "%s"', $this->getName());
	}
}
