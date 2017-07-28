<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view
 */

namespace Carrot3;

/**
 * Smartyレンダラー用の基底ビュー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SmartyView extends View {

	/**
	 * @access public
	 * @param Action $action 呼び出し元アクション
	 * @param string $suffix ビュー名サフィックス
	 * @param Renderer $renderer レンダラー
	 */
	public function __construct (Action $action, $suffix, Renderer $renderer = null) {
		parent::__construct($action, $suffix, $renderer);

		$this->setHeader('Content-Script-Type', MIMEType::getType('js'));
		$this->setHeader('Content-Style-Type', MIMEType::getType('css'));
	}

	/**
	 * 規定のレンダラーを生成して返す
	 *
	 * @access protected
	 * @return Renderer レンダラー
	 */
	protected function createDefaultRenderer () {
		return new Smarty;
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
			throw new ViewException(Utils::getClass($renderer) . 'をセットできません。');
		}

		parent::setRenderer($renderer, $flags);
		if (!$this->useragent->initializeView($this)) {
			throw new ViewException('ビューを初期化できません。');
		}

		if ($dir = $this->controller->getModule()->getDirectory('templates')) {
			$this->renderer->registerDirectory($dir);
		}
		if ($file = $this->getDefaultTemplate()) {
			$this->renderer->setTemplate($file);
		}
	}

	/**
	 * 規定のテンプレートを返す
	 *
	 * @access protected
	 * @param TemplateFile テンプレートファイル
	 */
	protected function getDefaultTemplate () {
		$names = [
			$this->getAction()->getName() . '.' . $this->getNameSuffix(),
			$this->getAction()->getName(),
		];
		foreach ($names as $name) {
			if ($file = $this->renderer->searchTemplate($name)) {
				return $file;
			}
		}
	}
}

