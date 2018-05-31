<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image.renderer
 */

namespace Carrot3;

/**
 * QRコードレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class QRCode implements ImageRenderer {
	private $gd;
	private $data;
	private $error;

	/**
	 * エンコード対象データを返す
	 *
	 * @access public
	 * @return string エンコード対象データ
	 */
	public function getData () {
		return $this->data;
	}

	/**
	 * エンコード対象データを設定
	 *
	 * @access public
	 * @param string $data エンコード対象データ
	 */
	public function setData ($data) {
		$this->data = $data;
		$service = new GoogleChartService;
		$this->gd = $service->getQRCodeImageFile($data)->getRenderer()->getGDHandle();
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType ():string {
		return MIMEType::getType('png');
	}

	/**
	 * GD画像リソースを返す
	 *
	 * @access public
	 * @return resource GD画像リソース
	 */
	public function getGDHandle () {
		return $this->gd;
	}

	/**
	 * 幅を返す
	 *
	 * @access public
	 * @return int 幅
	 */
	public function getWidth ():int {
		return imagesx($this->getGDHandle());
	}

	/**
	 * 高さを返す
	 *
	 * @access public
	 * @return int 高さ
	 */
	public function getHeight ():int {
		return imagesy($this->getGDHandle());
	}

	/**
	 * 送信内容を返す
	 *
	 * @access public
	 * @return string 送信内容
	 */
	public function getContents ():string {
		$image = new Image;
		$image->setType($this->getType());
		$image->setImage($this->getGDHandle());
		return $image->getContents();
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize ():int {
		return strlen($this->getContents());
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
		if (StringUtils::isBlank($this->getData())) {
			$this->error = 'データが未定義です。';
			return false;
		}
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError ():?string {
		return $this->error;
	}
}
