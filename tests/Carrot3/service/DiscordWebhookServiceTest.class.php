<?php
namespace Carrot3;

/**
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DiscordWebhookServiceTest extends Test {
	public function execute () {
		$message = new JSONRenderer;
		$message->setContents([
			'name' => BS_CARROT_NAME,
			'versioon' => BS_CARROT_VER,
		]);
		$response = (new DiscordWebhookService)->say($message);
		$this->assert('say', $response instanceof HTTPResponse);
	}
}
