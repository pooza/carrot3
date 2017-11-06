<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail
 */

namespace Carrot3;

/**
 * Smarty機能を内蔵したメールレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SmartyMail extends Mail {

	/**
	 * 既定レンダラーを生成して返す
	 *
	 * @access protected
	 * @return Renderer 既定レンダラー
	 */
	protected function createRenderer () {
		$renderer = new Smarty;
		$renderer->setType(MIMEType::getType('txt'));
		$renderer->setEncoding('utf-8');
		$renderer->addOutputFilter('mail');
		if ($module = $this->controller->getModule()) {
			if ($dir = $module->getDirectory('templates')) {
				$renderer->registerDirectory($dir);
			}
		}
		$renderer->setAttribute('date', Date::create());
		$renderer->setAttribute('client_host', $this->request->getHost());
		$renderer->setAttribute('server_host', $this->controller->getHost());
		return $renderer;
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @param integer $flags フラグのビット列
	 *   MIMEUtils::WITHOUT_HEADER ヘッダを修正しない
	 *   MIMEUtils::WITH_HEADER ヘッダも修正
	 */
	public function setRenderer (Renderer $renderer, $flags = MIMEUtils::WITH_HEADER) {
		if (!($renderer instanceof Smarty)) {
			throw new MailException('レンダラー形式が正しくありません。');
		}
		parent::setRenderer($renderer, $flags);
	}

	/**
	 * 送信
	 *
	 * @access public
	 * @param string $name 名前
	 * @param string $value 値
	 */
	public function send () {
		$this->getRenderer()->render();
		foreach ($this->getRenderer()->getHeaders() as $key => $value) {
			$this->setHeader($key, $value);
		}
		if ($file = $this->getFile()) {
			$file->delete();
		}
		parent::send();
	}
}
