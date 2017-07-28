<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * BASIC認証
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class BasicAuthFilter extends Filter {
	private function isAuthenticated () {
		if (StringUtils::isBlank($password = $this->controller->getAttribute('PHP_AUTH_PW'))) {
			return;
		}
		if (!Crypt::getInstance()->auth($this['password'], $password)) {
			return;
		}

		if (!StringUtils::isBlank($this['user_id'])) {
			return ($this['user_id'] == $this->controller->getAttribute('PHP_AUTH_USER'));
		}
		return Controller::COMPLETED;
	}

	public function initialize ($params = []) {
		$this['user_id'] = $this->controller->getAttribute('ADMIN_EMAIL');
		$this['password'] = $this->controller->getAttribute('ADMIN_PASSWORD');
		$this['realm'] = $this->controller->getHost()->getName();
		return parent::initialize($params);
	}

	public function execute () {
		if (!$this->isAuthenticated()) {
			View::putHeader('WWW-Authenticate: Basic realm=\'' . $this['realm'] . '\'');
			View::putHeader('Status: ' . HTTP::getStatus(401));
			return true;
		}
	}
}

