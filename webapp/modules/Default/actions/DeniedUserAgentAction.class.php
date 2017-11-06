<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class DeniedUserAgentAction extends C\Action {
	public function execute () {
		return C\View::ERROR;
	}
}
