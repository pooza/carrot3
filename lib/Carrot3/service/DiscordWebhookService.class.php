<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service
 */

namespace Carrot3;

/**
 * Doscord Webhookクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DiscordWebhookService extends CurlHTTP {
	const DEFAULT_HOST = 'discordapp.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param integer $port ポート
	 */
	public function __construct (Host $host = null, $port = null) {
		if (!$host) {
			$host = new Host(self::DEFAULT_HOST);
			$port = NetworkService::getPort('https');
		}
		parent::__construct($host, $port);
	}

	/**
	 * 話す
	 *
	 * @access public
	 * @param string $message
	 * @return HTTPResponse レスポンス
	 */
	public function say ($message) {
		if ($message instanceof StringFormat) {
			$message = $message->getContents();
		}
		$renderer = new JSONRenderer;
		$renderer->setContents(['content' => $message]);
		return $this->sendPOST(BS_SERVICE_DISCORD_WEBHOOK_URL, $renderer);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Discord Webフックサービス "%s"', $this->getName());
	}
}
