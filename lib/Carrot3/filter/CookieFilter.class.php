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
	private $cookieName;

	public function initialize ($params = []) {
		$this['cookie_error'] = 'Cookie機能が有効でない、又はセッションのタイムアウトです。';
		return parent::initialize($params);
	}

	public function execute () {
		if ($this->request instanceof ConsoleRequest) {
			return;
		}

		$this->cookieName = Crypt::digest($this->controller->getName('en'));
		switch ($this->request->getMethod()) {
			case 'HEAD':
			case 'GET':
				$time = Date::create()->setParameter('hour', '+' . BS_COOKIE_CHECKER_HOURS);
				$this->user->setAttribute($this->cookieName, true, $time);
				break;
			default:
				if (StringUtils::isBlank($this->user->getAttribute($this->cookieName))) {
					$this->request->setError('cookie', $this['cookie_error']);
				}
				break;
		}
	}
}

