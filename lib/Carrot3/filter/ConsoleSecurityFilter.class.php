<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage filter
 */

namespace Carrot3;

/**
 * コンソール認証
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConsoleSecurityFilter extends Filter {
	private function getRealUser () {
		return ltrim($this->controller->getAttribute('USER'), '_');
	}

	public function execute () {
		if (($user = $this->getRealUser()) != Process::getCurrentUser()) {
			$message = new StringFormat('実行ユーザー "%s" が正しくありません。');
			$message[] = $user;
			throw new ConsoleException($message);
		}
		if (PHP_SAPI != 'cli') {
			return Controller::COMPLETED;
		}
	}
}

