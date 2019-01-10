<?php
namespace Carrot3;

class QRCode implements ImageRenderer {
	private $gd;
	private $data;
	private $error;

	public function getData () {
		return $this->data;
	}

	public function setData ($data) {
		$this->data = $data;
		$service = new GoogleChartService;
		$this->gd = $service->getQRCodeImageFile($data)->getRenderer()->getGDHandle();
	}

	public function getType ():string {
		return MIMEType::getType('png');
	}

	public function getGDHandle () {
		return $this->gd;
	}

	public function getWidth ():int {
		return imagesx($this->getGDHandle());
	}

	public function getHeight ():int {
		return imagesy($this->getGDHandle());
	}

	public function getContents ():string {
		$image = new Image;
		$image->setType($this->getType());
		$image->setImage($this->getGDHandle());
		return $image->getContents();
	}

	public function getSize ():int {
		return strlen($this->getContents());
	}

	public function validate ():bool {
		if (StringUtils::isBlank($this->getData())) {
			$this->error = 'データが未定義です。';
			return false;
		}
		return true;
	}

	public function getError ():?string {
		return $this->error;
	}
}
