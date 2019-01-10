<?php
namespace Carrot3;

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

	public function __construct (?iterable $params = null) {
		if ($params) {
			if ($params['file'] && ($params['file'] instanceof \Carrot3\ImageFile)) {
				$this->setImage($params['file']->getContents());
			}
		}
	}

	public function __destruct () {
		unset($this->gd);
	}

	public function getGDHandle () {
		if (!$this->gd) {
			$this->gd = imagecreatetruecolor(self::DEFAULT_WIDTH, self::DEFAULT_HEIGHT);
			$this->fill($this->getBackgroundColor());
		}
		return $this->gd;
	}

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

	public function getBackgroundColor () {
		if (!$this->backgroundColor) {
			$this->backgroundColor = new Color(BS_IMAGE_THUMBNAIL_BGCOLOR);
		}
		return $this->backgroundColor;
	}

	public function setBackgroundColor (Color $color) {
		$this->backgroundColor = $color;
	}

	public function getType ():string {
		if (!$this->type) {
			$this->type = getimagesizefromstring($this->getContents())['mime'];
		}
		return $this->type;
	}

	public function setType (string $type) {
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

	public function getAspect () {
		return $this->getWidth() / $this->getHeight();
	}

	public function getWidth ():int {
		return imagesx($this->getGDHandle());
	}

	public function getHeight ():int {
		return imagesy($this->getGDHandle());
	}

	public function fill (Color $color) {
		imagefill(
			$this->getGDHandle(),
			$this->getOrigin()->getX(),
			$this->getOrigin()->getY(),
			$this->getColorID($color)
		);
	}

	protected function getColorID (Color $color) {
		return imagecolorallocatealpha(
			$this->getGDHandle(),
			$color['red'],
			$color['green'],
			$color['blue'],
			$color['alpha']
		);
	}

	public function createCoordinate (int $x, int $y) {
		return new Coordinate($this, $x, $y);
	}

	public function getOrigin () {
		if (!$this->origin) {
			$this->origin = $this->createCoordinate(0, 0);
		}
		return $this->origin;
	}

	public function getContents ():string {
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

	public function getSize ():int {
		return strlen($this->getContents());
	}

	public function resize (int $width, int $height) {
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

	public function resizeWidth (int $width) {
		if ($this->getWidth() < $width) {
			return;
		}
		$height = Numeric::round($this->getHeight() * ($width / $this->getWidth()));
		$this->resize($width, $height);
	}

	public function resizeHeight (int $height) {
		if ($this->getHeight() < $height) {
			return;
		}
		$width = Numeric::round($this->getWidth() * ($height / $this->getHeight()));
		$this->resize($width, $height);
	}

	public function resizeSquare (int $pixel) {
		if (($this->getWidth() < $pixel) && ($this->getHeight() < $pixel)) {
			return;
		}
		$this->resize($pixel, $pixel);
	}

	public function validate ():bool {
		if (!is_resource($this->getGDHandle())) {
			$this->error = 'GD画像リソースが正しくありません。';
			return false;
		}
		return true;
	}

	public function getError ():?string {
		return $this->error;
	}

	static public function getTypes () {
		if (!self::$types) {
			self::$types = Tuple::create();
			foreach (self::getSuffixes() as $suffix) {
				self::$types[$suffix] = MIMEType::getType($suffix);
			}
		}
		return self::$types;
	}

	static public function getSuffixes () {
		if (!self::$suffixes) {
			self::$suffixes = Tuple::create();
			foreach (['.gif', '.jpg', '.png'] as $suffix) {
				self::$suffixes[MIMEType::getType($suffix)] = $suffix;
			}
		}
		return self::$suffixes;
	}
}
