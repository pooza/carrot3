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
	use KeyGenerator;
	const DEFAULT_HOST = 'chart.apis.google.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param int $port ポート
	 */
	public function __construct (Host $host = null, int $port = null) {
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
	 * @param int $pixel 幅・高さ
	 * @param string $encoding エンコーディング、APIにchl値として渡す。
	 * @return ImageFile 画像ファイル
	 */
	public function getQRCodeImageFile ($data, int $pixel = 0, string $encoding = 'sjis-win') {
		if (!$pixel) {
			$pixel = BS_IMAGE_QRCODE_SIZE;
		}
		$params = Tuple::create([
			'chl' => StringUtils::convertEncoding($data, $encoding),
			'chld' => 'l|0',
		]);
		return $this->getImageFile('qr', $pixel, $pixel, $params);
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $type 種類
	 * @param int $witdh 幅
	 * @param int $height 高さ
	 * @param iterable $params パラメータ配列
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile ($type, int $width, int $height, iterable $params) {
		$key = $this->createKey([$type, $width, $height, $params]);
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
			} catch (\Throwable $e) {
				return null;
			}
		}
		return $file;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Google Chart "%s"', $this->getName());
	}
}
