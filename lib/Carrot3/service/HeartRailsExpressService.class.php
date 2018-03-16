<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service
 */

namespace Carrot3;

/**
 * HeartRails Expressクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class HeartRailsExpressService extends CurlHTTP {
	const DEFAULT_HOST = 'express.heartrails.com';
	const FORCE = 1;

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
	 * 最寄り駅を返す
	 *
	 * @access public
	 * @param Geocode $geocode ジオコード
	 * @param integer $flags フラグのビット列
	 *   self::FORCE 新規取得を強制
	 * @return Tuple 最寄り駅の配列
	 */
	public function getStations (Geocode $geocode, $flags = 0) {
		$key = BSCrypt::digest([
			Utils::getClass($this),
			__FUNCTION__,
			$geocode['lat'],
			$geocode['lng'],
		]);
		$date = Date::create()->setParameter('day', '-7');
		$serials = new SerializeHandler;
		if (($flags & self::FORCE) || !$serials->getAttribute($key, $date)) {
			try {
				$serials->setAttribute($key, $this->queryStations($geocode));
				$message = new StringFormat('%s,%sの最寄り駅を取得しました。');
				$message[] = $geocode['lat'];
				$message[] = $geocode['lng'];
				LogManager::getInstance()->put($message, $this);
			} catch (\Exception $e) {
			}
		}
		return $serials[$key];
	}

	private function queryStations (Geocode $geocode) {
		$url = $this->createRequestURL('/api/json');
		$url->setParameter('method', 'getStations');
		$url->setParameter('x', $geocode['lng']);
		$url->setParameter('y', $geocode['lat']);
		$response = $this->sendGET($url->getFullPath());

		$serializer = new JSONSerializer;
		$result = $serializer->decode($response->getRenderer()->getContents());

		$stations = Tuple::create();
		$x = null;
		$y = null;
		foreach ($result['response']['station'] as $entry) {
			if (($x !== $entry['x']) && ($y !== $entry['y'])) {
				$station = Tuple::create($entry);
				$station['line'] = Tuple::create($entry['line']);
				$stations[] = $station;
				$x = $entry['x'];
				$y = $entry['y'];
			} else {
				$station['line'][] = $entry['line'];
			}
		}
		return $stations;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('HeartRails Express "%s"', $this->getName());
	}
}
