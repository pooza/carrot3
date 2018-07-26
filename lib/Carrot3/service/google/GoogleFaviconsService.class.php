<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service.google
 */

namespace Carrot3;

/**
 * Google faviconsクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class GoogleFaviconsService extends CurlHTTP {
	const DEFAULT_HOST = 'www.google.com';

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
	 * faviconを返す
	 *
	 * @access public
	 * @param HTTPRedirector $url 対象URL
	 * @return Image PNG画像
	 */
	public function getFavicon (HTTPRedirector $url) {
		if ($file = $this->getImageFile($url['host'])) {
			return $file->getRenderer();
		}
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param Host $host 対象ドメイン
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile (Host $host) {
		$dir = FileUtils::getDirectory('favicon');
		$name = Crypt::digest($host->getName());
		if (!$file = $dir->getEntry($name, 'ImageFile')) {
			try {
				$url = $this->createRequestURL('/s2/favicons');
				$url->setParameter('domain', $host->getName());
				$response = $this->sendGET($url->getFullPath());
				$image = new Image;
				$image->setType(MIMEType::getType('.png'));
				$image->setImage($response->getRenderer()->getContents());
				$file = FileUtils::createTemporaryFile('.png', 'ImageFile');
				$file->setRenderer($image);
				$file->save();
				$file->setMode(0666);
				$file->rename($name);
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
		return sprintf('Google favicons "%s"', $this->getName());
	}
}
