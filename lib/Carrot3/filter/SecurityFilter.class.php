<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * クレデンシャル認証
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SecurityFilter extends Filter {
	public function initialize ($params = []) {
		$this['credential'] = $this->action->getCredential();
		return parent::initialize($params);
	}

	public function execute () {
		if (!$this->user->hasCredential($this['credential'])) {
			if ($this->action->deny()) {
				return Controller::COMPLETED;
			}
		}
	}

	/**
	 * 二度目も実行するか
	 *
	 * @access public
	 * @return bool 二度目も実行するならTrue
	 */
	public function isRepeatable () {
		return true;
	}
}
