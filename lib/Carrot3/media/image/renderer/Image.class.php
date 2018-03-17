<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image.renderer
 */

namespace Carrot3;

/**
 * GD画像レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Image implements ImageRenderer {
	protected $type;
	protected $gd;
	protected $origin;
	protected $contents;
	protected $backgroundColor;
	protected $error;
	static protected $types;
	static protected $suffixes;
	const DEFAULT_WIDTH = 320;
	const DEFAULT_HEIGHT = 240;

	/**
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 */
	public function __construct (ParameterHolder $params = null) {
		if ($params) {
			if ($params['file'] && ($params['file'] instanceof \Carrot3\ImageFile)) {
				$this->setImage($params['file']->getContents());
			}
		}
	}

	/**
	 * @access public
	 */
	public function __destruct () {
		unset($this->gd);
	}

	/**
	 * GD画像リソースを返す
	 *
	 * @access public
	 * @return resource GD画像リソース
	 */
	public function getGDHandle () {
		if (!$this->gd) {
			$this->gd = imagecreatetruecolor(self::DEFAULT_WIDTH, self::DEFAULT_HEIGHT);
			$this->fill($this->getBackgroundColor());
		}
		return $this->gd;
	}

	/**
	 * GD画像リソースを設定
	 *
	 * @access public
	 * @param mixed $image GD画像リソース等
	 */
	public function setImage ($image) {
		if (is_resource($image)) {
			$this->gd = $image;
		} else if ($image instanceof ImageRenderer) {
			$this->gd = $image->getGDHandle();
		} else if ($image instanceof ImageFile) {
			$this->gd = $image->getRenderer()->getGDHandle();
			$this->contents = $image->getContents();
		} else if (is_string($image)) {
			$this->gd = imagecreatefromstring($image);
			$this->contents = $image;
		} else {
			throw new ImageException('GD画像リソースが正しくありません。');
		}
	}

	/**
	 * 背景色を返す
	 *
	 * @access public
	 * @return Color 背景色
	 */
	public function getBackgroundColor () {
		if (!$this->backgroundColor) {
			$this->backgroundColor = new Color(BS_IMAGE_THUMBNAIL_BGCOLOR);
		}
		return $this->backgroundColor;
	}

	/**
	 * 背景色を設定
	 *
	 * @access public
	 * @param Color $color 背景色
	 */
	public function setBackgroundColor (Color $color) {
		$this->backgroundColor = $color;
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		if (!$this->type) {
			$this->type = getimagesizefromstring($this->getContents())['mime'];
		}
		return $this->type;
	}

	/**
	 * メディアタイプを設定
	 *
	 * @access public
	 * @param string $type メディアタイプ又は拡張子
	 */
	public function setType ($type) {
		if (!StringUtils::isBlank($suggested = MIMEType::getType($type, null))) {
			$type = $suggested;
		}
		if (!self::getTypes()->isContain($type)) {
			$message = new StringFormat('メディアタイプ"%s"が正しくありません。');
			$message[] = $type;
			throw new ImageException($message);
		}
		$this->type = $type;
		$this->contents = null;
	}

	/**
	 * 縦横比を返す
	 *
	 * @access public
	 * @return float 縦横比
	 */
	public function getAspect () {
		return $this->getWidth() / $this->getHeight();
	}

	/**
	 * 幅を返す
	 *
	 * @access public
	 * @return integer 幅
	 */
	public function getWidth () {
		return imagesx($this->getGDHandle());
	}

	/**
	 * 高さを返す
	 *
	 * @access public
	 * @return integer 高さ
	 */
	public function getHeight () {
		return imagesy($this->getGDHandle());
	}

	/**
	 * 塗る
	 *
	 * @access public
	 * @param Color $color 塗る色
	 */
	public function fill (Color $color) {
		imagefill(
			$this->getGDHandle(),
			$this->getOrigin()->getX(),
			$this->getOrigin()->getY(),
			$this->getColorID($color)
		);
	}

	/**
	 * 色IDを生成して返す
	 *
	 * @access protected
	 * @param Color $color 色
	 * @return integer 色ID
	 */
	protected function getColorID (Color $color) {
		return imagecolorallocatealpha(
			$this->getGDHandle(),
			$color['red'],
			$color['green'],
			$color['blue'],
			$color['alpha']
		);
	}

	/**
	 * 座標を生成して返す
	 *
	 * @access public
	 * @param integer $x X座標
	 * @param integer $y Y座標
	 * @return Coordinate 座標
	 */
	public function createCoordinate ($x, $y) {
		return new Coordinate($this, $x, $y);
	}

	/**
	 * 原点座標を返す
	 *
	 * @access public
	 * @return Coordinate 原点座標
	 */
	public function getOrigin () {
		if (!$this->origin) {
			$this->origin = $this->createCoordinate(0, 0);
		}
		return $this->origin;
	}

	/**
	 * 送信内容を返す
	 *
	 * @access public
	 * @return string 送信内容
	 */
	public function getContents () {
		if (StringUtils::isBlank($this->contents)) {
			ob_start();
			switch ($this->getType()) {
				case 'image/jpeg':
					imageinterlace($this->getGDHandle(), 1);
					imagejpeg($this->getGDHandle(), null, 100);
					break;
				case 'image/gif':
					imagegif($this->getGDHandle());
					break;
				case 'image/png':
					imagealphablending($this->getGDHandle(), false);
					imagesavealpha($this->getGDHandle(), true);
					imagepng($this->getGDHandle());
					break;
			}
			$this->contents = ob_get_contents();
			ob_end_clean();
		}
		return $this->contents;
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return integer サイズ
	 */
	public function getSize () {
		return strlen($this->getContents());
	}

	/**
	 * サイズ変更
	 *
	 * @access public
	 * @param integer $width 幅
	 * @param integer $height 高さ
	 */
	public function resize ($width, $height) {
		$dest = new Image;
		$dest->setImage(imagecreatetruecolor(
			Numeric::round($width),
			Numeric::round($height)
		));
		$dest->fill($this->getBackgroundColor());

		if ($this->getAspect() < $dest->getAspect()) {
			$width = ceil($dest->getHeight() * $this->getAspect());
			$x = Numeric::round(($dest->getWidth() - $width) / 2);
			$coord = $dest->createCoordinate($x, 0);
		} else {
			$height = ceil($dest->getWidth() / $this->getAspect());
			$y = Numeric::round(($dest->getHeight() - $height) / 2);
			$coord = $dest->createCoordinate(0, $y);
		}

		imagecopyresampled(
			$dest->getGDHandle(), //コピー先
			$this->getGDHandle(), //コピー元
			$coord->getX(), $coord->getY(),
			$this->getOrigin()->getX(), $this->getOrigin()->getY(),
			Numeric::round($width), Numeric::round($height), //コピー先サイズ
			$this->getWidth(), $this->getHeight() //コピー元サイズ
		);
		$this->setImage($dest);
	}

	/**
	 * 幅変更
	 *
	 * @access public
	 * @param integer $width 幅
	 */
	public function resizeWidth ($width) {
		if ($this->getWidth() < $width) {
			return;
		}
		$height = Numeric::round($this->getHeight() * ($width / $this->getWidth()));
		$this->resize($width, $height);
	}

	/**
	 * 高さ変更
	 *
	 * @access public
	 * @param integer $height 高さ
	 */
	public function resizeHeight ($height) {
		if ($this->getHeight() < $height) {
			return;
		}
		$width = Numeric::round($this->getWidth() * ($height / $this->getHeight()));
		$this->resize($width, $height);
	}

	/**
	 * 長辺を変更
	 *
	 * @access public
	 * @param integer $pixel 長辺
	 */
	public function resizeSquare ($pixel) {
		if (($this->getWidth() < $pixel) && ($this->getHeight() < $pixel)) {
			return;
		}
		$this->resize($pixel, $pixel);
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		if (!is_resource($this->getGDHandle())) {
			$this->error = 'GD画像リソースが正しくありません。';
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
	public function getError () {
		return $this->error;
	}

	/**
	 * 利用可能なメディアタイプを返す
	 *
	 * @access public
	 * @return Tuple メディアタイプ
	 * @static
	 */
	static public function getTypes () {
		if (!self::$types) {
			self::$types = Tuple::create();
			foreach (self::getSuffixes() as $suffix) {
				self::$types[$suffix] = MIMEType::getType($suffix);
			}
		}
		return self::$types;
	}

	/**
	 * 利用可能な拡張子を返す
	 *
	 * @access public
	 * @return Tuple 拡張子
	 * @static
	 */
	static public function getSuffixes () {
		if (!self::$suffixes) {
			$suffixes = Tuple::create(['.gif', '.jpg', '.png']);
			if (extension_loaded('imagick')) {
				$suffixes->merge(['.tif', '.eps', '.ico', '.pdf']);
			}
			if (extension_loaded('gmagick')) {
				$suffixes->merge(['.tif', '.eps', '.pdf']);
			}

			self::$suffixes = Tuple::create();
			foreach ($suffixes->uniquize() as $suffix) {
				self::$suffixes[MIMEType::getType($suffix)] = $suffix;
			}
		}
		return self::$suffixes;
	}
}
