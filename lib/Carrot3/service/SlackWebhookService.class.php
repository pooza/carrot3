<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service
 */

namespace Carrot3;

/**
 * Slack Webhookクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SlackWebhookService extends CurlHTTP {
	const DEFAULT_HOST = 'hooks.slack.com';

	/**
	 * @access public
	 * @param Host $host ホスト
	 * @param int $port ポート
	 */
	public function __construct (Host $host = null, int $port = null) {
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
	 * @param MessageContainer $message
	 * @return HTTPResponse レスポンス
	 */
	public function say (MessageContainer $message) {
		$renderer = new JSONRenderer;
		$renderer->setContents(['text' => $message->getMessage()]);
		return $this->sendPOST(BS_SERVICE_SLACK_WEBHOOK_URL, $renderer);
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Slack Webフックサービス "%s"', $this->getName());
	}
}
