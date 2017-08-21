<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * Cookieのサポートをチェックするフィルタ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class CookieFilter extends Filter {
	public function initialize ($params = []) {
		$this['cookie_error'] = 'Cookie機能が有効でない、又はセッションのタイムアウトです。';
		$this['excluded_actions'] = ['Login'];
		return parent::initialize($params);
	}

	public function execute () {
		if ($this->request instanceof ConsoleRequest) {
			return;
		}
		switch ($this->request->getMethod()) {
			case 'HEAD':
			case 'GET':
				$time = Date::create()->setParameter('hour', '+' . BS_COOKIE_CHECKER_HOURS);
				$this->user->setAttribute($this->createKey(), true, $time);
				break;
			default:
				if (StringUtils::isBlank($this->user->getAttribute($this->createKey()))) {
					$this->request->setError('cookie', $this['cookie_error']);
				}
				break;
		}
	}

	private function createKey () {
		return Crypt::digest($this->controller->getName('en'));
	}
}

