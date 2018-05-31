<?php
namespace Carrot3;

class ImageFile extends MediaFile {
	protected $renderer;
	protected $rendererClass;
	protected $rendererParameters;

	public function __construct ($path, $class = null) {
		if (StringUtils::isBlank($class)) {
			$class = ImageManager::getRendererEntries()['default'];
		}
		if (is_iterable($class)) {
			$params = Tuple::create($class);
			$class = $params['class'];
			$this->rendererParameters = $params;
		}
		$this->rendererClass = $this->loader->getClass($class);
		parent::__construct($path);
	}

	public function __call ($method, $values) {
		return Utils::executeMethod($this->getRenderer(), $method, $values);
	}

	public function rename (string $name) {
		$name .= Image::getSuffixes()[$this->getRenderer()->getType()];
		parent::rename($name);
	}

	protected function analyze () {
		try {
			File::analyze();
			$this->attributes['type'] = $this->getRenderer()->getType();
			$this->attributes['width'] = (int)$this->getRenderer()->getWidth();
			$this->attributes['height'] = (int)$this->getRenderer()->getHeight();
			$this->attributes['pixel_size'] = $this['width'] . '×' . $this['height'];
		} catch (\Exception $e) {
			$this->attributes['error'] = $e->getMessage();
		}
	}

	public function getRenderer () {
		if (!$this->renderer) {
			if (!$this->isExists() || !$this->getSize()) {
				throw new ImageException($this . 'の形式が不明です。');
			}
			$params = Tuple::create($this->rendererParameters);
			$params['file'] = $this;
			$this->renderer = new $this->rendererClass($params);
		}
		return $this->renderer;
	}

	public function setRenderer (ImageRenderer $renderer) {
		$this->renderer = $renderer;
		$this->rendererClass = Utils::getClass($renderer);
		$this->attributes = null;
	}

	public function save () {
		if ($this->isExists() && !$this->isWritable()) {
			throw new FileException($this . 'に書き込むことができません。');
		}

		$this->removeImageCache('image');
		$this->setContents($this->getRenderer()->getContents());
	}

	public function createElement (iterable $params, UserAgent $useragent = null) {
		$params = Tuple::create($params);
		$this->resizeByWidth($params, $useragent);

		$element = new ImageElement;
		$element->setURL($this->createURL($params));
		$element->registerStyleClass($params['style_class']);
		$element->setAttribute('width', $this['width']);
		$element->setAttribute('height', $this['height']);
		$element->setAttributes($params);
		return $element;
	}

	public function removeImageCache (string $size) {
		(new ImageManager)->removeEntry($this, $size);
	}

	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		return (new ImageManager)->getInfo($this, $size, $pixel, $flags);
	}

	public function getImageFile (string $size):?ImageFile {
		return $this;
	}

	public function setImageFile (string $name, ImageFile $file) {
		$this->getRenderer()->setImage($file);
		$this->save();
	}

	public function validate ():bool {
		if (!parent::validate()) {
			return false;
		}
		return ($this->getMainType() == 'image');
	}

	public function __toString () {
		return sprintf('画像ファイル "%s"', $this->getShortPath());
	}
}
