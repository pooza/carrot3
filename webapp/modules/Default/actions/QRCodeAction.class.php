<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class QRCodeAction extends C\Action {
	public function execute () {
		$qrcode = new C\QRCode;
		$qrcode->setData($this->request['value']);
		$this->request->setAttribute('renderer', $qrcode);
		return C\View::SUCCESS;
	}

	public function digest () {
		if (!$this->digest) {
			$this->digest = C\Crypt::digest([
				$this->request['value'],
				$this->controller->getHost()->getName(),
				$this->getModule()->getName(),
				$this->getName(),
			]);
		}
		return $this->digest;
	}

	public function handleError () {
		$this->request->setAttribute(
			'renderer',
			C\FileUtils::getDirectory('images')->getEntry('spacer.gif')
		);
		return C\View::ERROR;
	}

	public function isCacheable () {
		return !$this->request->hasErrors();
	}
}
