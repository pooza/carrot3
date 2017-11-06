<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage DevelopTableReport
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DevelopTableReportModule;
use \Carrot3 as C;

class DefaultAction extends C\Action {
	public function execute () {
		return $this->getModule()->getAction('DatabaseList')->forward();
	}

	public function handleError () {
		return $this->execute();
	}
}
