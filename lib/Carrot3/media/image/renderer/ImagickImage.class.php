<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image.renderer
 */

namespace Carrot3;

/**
 * ImageMagick画像レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ImagickImage extends Image {
	protected $imagick;
	protected $method = 'thumbnail';

	/**
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 */
	public function __construct (ParameterHolder $params = null) {
		if (!extension_loaded('imagick')) {
			throw new ImageException('imagickモジュールがロードされていません。');
		}
		if ($params) {
			if ($params['file'] && ($params['file'] instanceof \Carrot3\ImageFile)) {
				$this->setImagick(new \Imagick($params['file']->getPath()));
			}
			if ($params['method']) {
				$this->setResizeMethod($params['method']);
			}
		}
	}

	/**
	 * @access public
	 */
	public function __destruct () {
		parent::__destruct();
		unset($this->imagick);
	}

	/**
	 * Imagickオブジェクトを返す
	 *
	 * @access public
	 * @return Imagick
	 */
	public function getImagick () {
		if (!$this->imagick) {
			$this->imagick = new \Imagick;
			$this->imagick->newImage(
				self::DEFAULT_WIDTH,
				self::DEFAULT_HEIGHT,
				$this->getBackgroundColor()->getContents()
			);
			$this->setType(BS_IMAGE_THUMBNAIL_TYPE);
		}
		return $this->imagick;
	}

	/**
	 * Imagickオブジェクトを設定
	 *
	 * @access public
	 * @param Imagick $imagick
	 */
	public function setImagick (\Imagick $imagick) {
		$this->imagick = $imagick;
	}

	/**
	 * GD画像リソースを返す
	 *
	 * @access public
	 * @return resource GD画像リソース
	 */
	public function getGDHandle () {
		$image = new Image;
		$image->setType($this->getType());
		$image->setImage($this->getContents());
		return $image->getGDHandle();
	}

	/**
	 * GD画像リソースを設定
	 *
	 * @access public
	 * @param mixed $image GD画像リソース等
	 */
	public function setImage ($image) {
		$renderer = null;
		if ($image instanceof ImageRenderer) {
			$renderer = $image;
		} else if ($image instanceof ImageFile) {
			$renderer = $image->getRenderer();
		}
		if ($renderer && ($renderer instanceof self)) {
			$this->setImagick($renderer->getImagick());
			return;
		}
		return parent::setImage($image);
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		switch ($type = $this->getImagick()->getImageMimeType()) {
			case 'image/x-ico':
				return MIMEType::getType('ico');
		}
		return $type;
	}

	/**
	 * メディアタイプを設定
	 *
	 * @access public
	 * @param string $type メディアタイプ又は拡張子
	 */
	public function setType ($type) {
		if (StringUtils::isBlank($suffix = MIMEType::getSuffix($type))) {
			$message = new StringFormat('"%s"は正しくないMIMEタイプです。');
			$message[] = $type;
			throw new ImageException($message);
		}
		$this->getImagick()->setImageFormat(ltrim($suffix, '.'));
	}

	/**
	 * 幅を返す
	 *
	 * @access public
	 * @return integer 幅
	 */
	public function getWidth () {
		return $this->getImagick()->getImageWidth();
	}

	/**
	 * 高さを返す
	 *
	 * @access public
	 * @return integer 高さ
	 */
	public function getHeight () {
		return $this->getImagick()->getImageHeight();
	}

	/**
	 * 塗る
	 *
	 * @access public
	 * @param Color $color 塗る色
	 */
	public function fill (Color $color) {
		$this->getImagick()->floodFillPaintImage(
			$color->getContents(),
			0,
			$this->getImagick()->getImagePixelColor(
				$this->getOrigin()->getX(),
				$this->getOrigin()->getY()
			),
			$this->getOrigin()->getX(),
			$this->getOrigin()->getY(),
			false
		);
	}

	/**
	 * 送信内容を返す
	 *
	 * @access public
	 * @return string 送信内容
	 */
	public function getContents () {
		return (string)$this->getImagick();
	}

	/**
	 * リサイズ関数を設定
	 *
	 * @access public
	 * @param string $function 関数名
	 */
	public function setResizeMethod ($method) {
		$this->method = $method;
	}

	/**
	 * サイズ変更
	 *
	 * @access public
	 * @param integer $width 幅
	 * @param integer $height 高さ
	 */
	public function resize ($width, $height) {
		$dest = new ImagickImage;
		$dest->setImagick(new \Imagick);
		$dest->getImagick()->newImage(
			Numeric::round($width),
			Numeric::round($height),
			$this->getBackgroundColor()->getContents()
		);
		$dest->setType($this->getType());
		if ($this->getAspect() < $dest->getAspect()) {
			$width = ceil($dest->getHeight() * $this->getAspect());
			$x = Numeric::round(($dest->getWidth() - $width) / 2);
			$coord = $dest->createCoordinate($x, 0);
		} else {
			$height = ceil($dest->getWidth() / $this->getAspect());
			$y = Numeric::round(($dest->getHeight() - $height) / 2);
			$coord = $dest->createCoordinate(0, $y);
		}

		$resized = clone $this->getImagick();
		switch ($this->method) {
			case '':
			case 'thumbnail':
				$resized->thumbnailImage(Numeric::round($width), Numeric::round($height), false);
				break;
			case 'resize':
				$resized->resizeImage(Numeric::round($width), Numeric::round($height), 0, 1);
				break;
			default:
				$method = $this->method . 'Image';
				$resized->$method(Numeric::round($width), Numeric::round($height));
				break;
		}
		$dest->getImagick()->compositeImage(
			$resized,
			\Imagick::COMPOSITE_DEFAULT,
			$coord->getX(), $coord->getY()
		);
		$this->setImagick($dest->getImagick());
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return boolean 出力可能ならTrue
	 */
	public function validate () {
		if (StringUtils::isBlank($this->getContents())) {
			$this->error = 'Imagick画像リソースが正しくありません。';
			return false;
		}
		return true;
	}
}
