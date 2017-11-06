<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage service
 */

namespace Carrot3;

/**
 * PubSubHubbubクライアント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PublisherService extends CurlHTTP {
	protected $url;

	/**
	 * APIのURLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url 対象URL
	 */
	public function setURL (HTTPRedirector $url) {
		$this->url = $url;
	}

	/**
	 * 発行
	 *
	 * @access public
	 * @param HTTPRedirector $url 対象URL
	 * @return boolean 成功ならTrue
	 */
	public function publish (HTTPRedirector $url) {
		if (!$this->url) {
			throw new NetException($this . 'のURLが未設定です。');
		}
		$url = $url->getURL();
		$values = new WWWFormRenderer;
		$values['hub.mode'] = 'publish';
		$values['hub.url'] = $url->getContents();
		$response = $this->sendPOST($this->url->getFullPath(), $values);
		if ($response->getStatus() != 204) {
			throw new NetException($this . ' に ' . $url . ' を発行できませんでした。');
		}
		LogManager::getInstance()->put($this . ' に ' . $url . ' を発行しました。', $this);
		return true;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('Hubサービス "%s"', $this->getName());
	}
}
