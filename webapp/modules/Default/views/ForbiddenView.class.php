<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class ForbiddenView extends C\SmartyView {
	public function execute () {
		$this->setStatus(403);
	}
}
