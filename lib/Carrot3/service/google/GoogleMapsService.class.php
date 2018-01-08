<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google Mapsクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleMapsService extends CurlHTTP {
	private $table;
	private $useragent;
	const DEFAULT_HOST = 'maps.google.com';

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
	 * 要素を返す
	 *
	 * @access public
	 * @param string $address 住所等
	 * @param ParameterHolder $params パラメータ配列
	 * @return DivisionElement
	 */
	public function createElement ($address, ParameterHolder $params = null) {
		$params = Tuple::create($params);
		$params['address'] = $address;
		if (!$params['zoom']) {
			$params['zoom'] = BS_SERVICE_GOOGLE_MAPS_ZOOM;
		}

		if (!$geocode = $this->getGeocode($address)) {
			$message = new StringFormat('"%s" のジオコードが取得できません。');
			$message[] = $address;
			throw new ServiceException($message);
		}

		$info = $this->useragent->getDisplayInfo();
		if (!$params['max_width'] && $info['width']) {
			$params['max_width'] = $info['width'];
		}
		if ($params['max_width'] && ($params['max_width'] < $params['width'])) {
			$params['width'] = $params['max_width'];
			$params['height'] = Numeric::round(
				$params['height'] * $params['width'] / $params['max_width']
			);
		}
		return $geocode->createElement($params);
	}

	/**
	 * ジオコードを返す
	 *
	 * @access public
	 * @param string $address 住所等
	 * @return Geocode ジオコード
	 */
	public function getGeocode ($address) {
		return (new GoogleMapsGeocodingService)->getGeocode($address);
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
		$url->setParameter('key', BS_SERVICE_GOOGLE_MAPS_API_KEY);
		return $url;
	}

	/**
	 * img要素を返す
	 *
	 * @access protected
	 * @param Geocode $geocode ジオコード
	 * @param Tuple $params パラメータ配列
	 * @return DivisionElement
	 */
	protected function createImageElement (Geocode $geocode, Tuple $params) {
		$address = $params['address'];
		$params->removeParameter('address');
		$file = $this->getImageFile($geocode, $params);
		$info = $file->getImageInfo('roadmap');

		$image = new ImageElement;
		$image->setURL(URL::create($info['url']));
		$container = new DivisionElement;
		if (StringUtils::isBlank($label = $params['label'])) {
			$anchor = $container->addElement(new AnchorElement);
			$anchor->link($image, $this->createPageURL($address, $params));
		} else {
			$container->addElement($image);
			$labelContainer = $container->addElement(new DivisionElement);
			$labelContainer->setAttribute('align', 'center');
			$anchor = $labelContainer->addElement(new AnchorElement);
			$anchor->setBody($label);
			$anchor->setURL($this->createPageURL($address, $params));
		}
		return $container;
	}

	private function createPageURL ($address, Tuple $params) {
		$url = URL::create();
		$url['host'] = self::DEFAULT_HOST;
		if ($geocode = $this->getGeocode($address)) {
			$url->setParameter('ll', $geocode->format());
		}
		if ($params['zoom']) {
			$url->setParameter('z', $params['zoom']);
		}
		return $url;
	}

	/**
	 * 地図画像ファイルを返す
	 *
	 * @access protected
	 * @param Geocode $geocode ジオコード
	 * @param Tuple $params パラメータ配列
	 * @return ImageFile 画像ファイル
	 */
	protected function getImageFile (Geocode $geocode, Tuple $params) {
		$dir = FileUtils::getDirectory('maps');
		$name = Crypt::digest([
			$geocode->format(),
			$params->join('|'),
		]);
		if (!$file = $dir->getEntry($name, 'ImageFile')) {
			$response = $this->sendGET($this->getImageURL($geocode, $params)->getFullPath());
			$image = new Image;
			$image->setImage($response->getRenderer()->getContents());
			$file = $dir->createEntry($name, 'ImageFile');
			$file->setRenderer($image);
			$file->save();
		}
		return $file;
	}

	/**
	 * Google Static Maps APIのクエリーURLを返す
	 *
	 * @access protected
	 * @param Geocode $geocode ジオコード
	 * @param Tuple $params パラメータ配列
	 * @return HTTPURL クエリーURL
	 * @link http://code.google.com/intl/ja/apis/maps/documentation/staticmaps/
	 */
	protected function getImageURL (Geocode $geocode, Tuple $params) {
		$info = $this->useragent->getDisplayInfo();
		$size = new StringFormat('%dx%d');
		$size[] = $info['width'];
		$size[] = Numeric::round($info['width'] * 0.75);

		$url = $this->createRequestURL('/maps/api/staticmap');
		$url->setParameter('format', BS_SERVICE_GOOGLE_STATIC_MAPS_FORMAT);
		$url->setParameter('maptype', 'mobile');
		$url->setParameter('center', $geocode->format());
		$url->setParameter('markers', $geocode->format());
		$url->setParameter('size', $size->getContents());
		$url->setParameter('key', BS_SERVICE_GOOGLE_STATIC_MAPS_API_KEY);
		foreach ($params as $key => $value) {
			$url->setParameter($key, $value);
		}
		return $url;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Google Maps "%s"', $this->getName());
	}
}
