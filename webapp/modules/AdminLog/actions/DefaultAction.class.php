<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage AdminLog
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\AdminLogModule;
use \Carrot3 as C;

class DefaultAction extends C\Action {
	public function execute () {
		return $this->getModule()->getAction('Browse')->forward();
	}

	public function handleError () {
		return $this->execute();
	}
}
