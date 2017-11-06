<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage Default
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3\DefaultModule;
use \Carrot3 as C;

class LogoutAction extends C\Action {
	public function execute () {
		$this->user->logout();
		return $this->getModule()->getAction('Login')->redirect();
	}
}
