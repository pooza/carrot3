<?php
namespace Carrot3;

class MovieFile extends MediaFile {
	protected function analyze () {
		parent::analyze();
		if (mb_ereg('frame rate: [^\\-]+ -> ([.[:digit:]]+)', $this->output, $matches)) {
			$this->attributes['frame_rate'] = (float)$matches[1];
		}
		if (mb_ereg(' ([[:digit:]]{2,4})x([[:digit:]]{2,4})', $this->output, $matches)) {
			$info = $this->getImageInfo('image');
			$this->attributes['width'] = (int)$info['width'];
			$this->attributes['height'] = (int)$info['height'];
			$this->attributes['pixel_size'] = $this['width'] . '×' . $this['height'];
			$this->attributes['aspect'] = $this['width'] / $this['height'];
		}
	}

	public function hasMovieTrack ():bool {
		if (!$this->attributes->count()) {
			$this->analyze();
		}
		return ($this['width'] && $this['height']);
	}

	public function convert (MediaConvertor $convertor = null) {
		if (!$convertor) {
			$convertor = new MPEG4MediaConvertor;
		}
		return $convertor->execute($this);
	}

	public function createElement (iterable $params, UserAgent $useragent = null) {
		switch ($params['mode']) {
			case 'lity':
				return $this->createLityElement($params);
			default:
				return $this->createVideoElement($params);
		}
	}

	public function createLityElement (iterable $params) {
		$params = Tuple::create($params);
		if (!$params['width_movie']) {
			$params['width_movie'] = $params['width'];
		}
		if (!$params['height_movie']) {
			$params['height_movie'] = $params['height'];
		}

		$container = new DivisionElement;
		$anchor = $container->addElement(new LityAnchorElement);
		$id = Crypt::digest([Utils::getClass($this), $this->getID(), Numeric::getRandom()]);
		$anchor->setURL('#' . $id);
		if ($info = $params['thumbnail']) {
			$image = $anchor->addElement(new ImageElement);
			$image->setAttributes(Tuple::create($info));
		} else {
			$anchor->setBody($params['label']);
		}

		$paramsInner = clone $params;
		$paramsInner['mode'] = null;
		$inner = $container->addElement($this->createElement($paramsInner));
		$inner->setID($id);
		$inner->registerStyleClass('lity-hide');
		return $container;
	}

	public function createVideoElement (iterable $params) {
		$this->resizeByWidth($params, $useragent);
		$element = new VideoElement;
		$element->registerSource($this->createURL($params));
		$element->setAttribute('width', $params['width']);
		$element->setAttribute('height', $params['height']);
		return $element->wrap(new DivisionElement);
	}

	public function validate ():bool {
		if (!parent::validate()) {
			return false;
		}
		return ($this->getMainType() == 'video');
	}

	public function getImageFile (string $size):?ImageFile {
		$dir = FileUtils::getDirectory('file_thumbnail');
		if ($file = $dir->getEntry($this->getID(), 'ImageFile')) {
			return $file;
		}
		$file = new ImageFile($this->convert(new PNGMediaConvertor)->getPath());
		$file->setName($this->getID());
		$file->moveTo($dir);
		return $file;
	}

	public function __toString () {
		return sprintf('動画ファイル "%s"', $this->getShortPath());
	}
}
