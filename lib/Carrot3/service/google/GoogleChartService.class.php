<?php
namespace Carrot3;

class GoogleChartService extends CurlHTTP {
	use KeyGenerator;
	const DEFAULT_HOST = 'chart.apis.google.com';

	public function __construct (Host $host = null, int $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
		}
		parent::__construct($host, $port);
	}

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

	public function getImageFile ($type, int $width, int $height, iterable $params) {
		$key = $this->createKey([$type, $width, $height, $params]);
		$dir = FileUtils::getDirectory('chart');
		if (!$file = $dir->getEntry($key, 'ImageFile')) {
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
		}
		return $file;
	}

	public function __toString () {
		return sprintf('Google Chart "%s"', $this->getName());
	}
}
