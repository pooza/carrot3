<?php
namespace Carrot3;

class ImagickImage extends Image {
	protected $imagick;
	protected $method = 'thumbnail';

	public function __construct (?iterable $params = null) {
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

	public function __destruct () {
		parent::__destruct();
		unset($this->imagick);
	}

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

	public function setImagick (\Imagick $imagick) {
		$this->imagick = $imagick;
	}

	public function getGDHandle () {
		$image = new Image;
		$image->setType($this->getType());
		$image->setImage($this->getContents());
		return $image->getGDHandle();
	}

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

	public function getType ():string {
		switch ($type = $this->getImagick()->getImageMimeType()) {
			case 'image/x-ico':
				return MIMEType::getType('ico');
		}
		return $type;
	}

	public function setType (string $type) {
		if (StringUtils::isBlank($suffix = MIMEType::getSuffix($type))) {
			$message = new StringFormat('"%s"は正しくないMIMEタイプです。');
			$message[] = $type;
			throw new ImageException($message);
		}
		$this->getImagick()->setImageFormat(ltrim($suffix, '.'));
	}

	public function getWidth ():int {
		return $this->getImagick()->getImageWidth();
	}

	public function getHeight ():int {
		return $this->getImagick()->getImageHeight();
	}

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

	public function getContents ():string {
		return (string)$this->getImagick();
	}

	public function setResizeMethod ($method) {
		$this->method = $method;
	}

	public function resize (int $width, int $height) {
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

	public function validate ():bool {
		if (StringUtils::isBlank($this->getContents())) {
			$this->error = 'Imagick画像リソースが正しくありません。';
			return false;
		}
		return true;
	}
}
