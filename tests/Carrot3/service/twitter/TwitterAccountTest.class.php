<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TwitterAccountTest extends Test {
	public function execute () {
		if (!StringUtils::isBlank(BS_AUTHOR_TWITTER)) {
			$account = new TwitterAccount(BS_AUTHOR_TWITTER);
			$message = Date::create()->format('YmdHis') . ' ' . $this->controller->getName();
			try {
				$response = $account->tweet($message);
				$this->assert('tweet', $response instanceof JSONRenderer);
			} catch (\Exception $e) {
			}

			try {
				$response = $account->sendDirectMessage(
					$message,
					new TwitterAccount(BS_ADMIN_TWITTER)
				);
				$this->assert('sendDirectMessage', $response instanceof JSONRenderer);
			} catch (\Exception $e) {
			}
		}
	}
}
