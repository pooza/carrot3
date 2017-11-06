<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google Chartクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleChartService extends CurlHTTP {
	const DEFAULT_HOST = 'chart.apis.google.com';

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
	 * QRコードの画像ファイルを返す
	 *
	 * @access public
	 * @param string $data 対象データ
	 * @return ImageFile 画像ファイル
	 */
	public function getQRCodeImageFile ($data, $size = 0, $encoding = 'sjis-win') {
		if (!$size) {
			$size = BS_IMAGE_QRCODE_SIZE;
		}
		$params = Tuple::create([
			'chl' => StringUtils::convertEncoding($data, $encoding),
			'chld' => 'l|0',
		]);
		return $this->getImageFile('qr', $size, $size, $params);
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $type 種類
	 * @param integer $witdh 幅
	 * @param integer $height 高さ
	 * @param ParameterHolder $params パラメータ配列
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile ($type, $width, $height, ParameterHolder $params) {
		$key = $this->createKey($type, $width, $height, $params);
		$dir = FileUtils::getDirectory('chart');
		if (!$file = $dir->getEntry($key, 'ImageFile')) {
			try {
				$url = $this->createRequestURL('/chart');
				$url->setParameter('cht', $type);
				$url->setParameter('chs', $width . 'x' . $height);
				$url->setParameters($params);
				$response = $this->sendGET($url->getFullPath());

				$image = new Image;
				$image->setType(MIMEType::getType('.png'));
				$image->setImage($response->getRenderer()->getContents());
				$file = FileUtils::createTemporaryFile('.png', 'ImageFile');
				$file->setRenderer($image);
				$file->save();
				$file->setMode(0666);
				$file->rename($key);
				$file->moveTo($dir);
			} catch (\Exception $e) {
			}
		}
		return $file;
	}

	private function createKey ($type, $width, $height, ParameterHolder $params) {
		$values = Tuple::create();
		$values['type'] = $type;
		$values['width'] = $width;
		$values['height'] = $height;
		$values['params'] = Tuple::create($params->getParameters());
		$serializer = new PHPSerializer;
		return Crypt::digest($serializer->encode($values->decode()));
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Google Chart "%s"', $this->getName());
	}
}
