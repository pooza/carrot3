<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class PingAction extends C\Action {
	public function execute () {
		try {
			C\Database::getInstance();
			return C\View::SUCCESS;
		} catch (\Exception $e) {
			$this->request->setError('database', $e->getMessage());
			return C\View::ERROR;
		}
	}

	protected function getViewClass () {
		return 'JSONView';
	}
}

