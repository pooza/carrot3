<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service
 */

namespace Carrot3;

/**
 * piconクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PiconService extends CurlHTTP {
	const DEFAULT_HOST = 'localhost';
	const DEFAULT_PORT = 3000;

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param int $port ポート
	 */
	public function __construct (Host $host = null, int $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
		}
		if (!$port) {
			$port = self::DEFAULT_PORT;
		}
		parent::__construct($host, $port);
	}

	/**
	 * サイズ変更
	 *
	 * @access public
	 * @param ImageContainer $image 対象画像
	 * @param int $width 幅
	 * @param int $height 高さ
	 */
	public function resize (ImageRenderer $image, int $width, int $height) {
		$params = new WWWFormRenderer;
		$params['width'] = $width;
		$params['height'] = $height;
		$params['background_color'] = BS_IMAGE_THUMBNAIL_BGCOLOR;
		$response = $this->sendPOST('/resize', $params, $this->createFile($image));
		$image->setImage($response->getRenderer()->getContents());
	}

	/**
	 * 幅変更
	 *
	 * @access public
	 * @param ImageContainer $image 対象画像
	 * @param int $width 幅
	 * @param string $method リサイズ関数
	 */
	public function resizeWidth (ImageRenderer $image, int $width, $method = 'thumbnail') {
		$params = new WWWFormRenderer;
		$params['width'] = $width;
		$params['method'] = $method;
		$response = $this->sendPOST('/resize_width', $params, $this->createFile($image));
		$image->setImage($response->getRenderer()->getContents());
	}

	/**
	 * アップロードすべきファイルを生成して返す
	 *
	 * @access protected
	 * @return ImageFile
	 */
	protected function createFile (ImageRenderer $image) {
		if ($image instanceof ImageFile) {
			return clone $image;
		} else {
			$file = FileUtils::createTemporaryFile(
				MIMEType::getSuffix($image->getType())
			);
			$file->setContents($image->getContents());
			return new ImageFile($file->getPath());
		}
	}

	/**
	 * サービスの情報を返す
	 *
	 * @access public
	 * @return JSONRenderer 結果文書
	 */
	public function getInfo () {
		$response = $this->sendGET('/about');
		$json = new JSONRenderer;
		$json->setContents(
			json_decode($response->getRenderer()->getContents(), true)
		);
		return $json;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('picon "%s"', $this->getName());
	}
}
