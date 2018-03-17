<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view
 */

namespace Carrot3;

/**
 * API結果文書用 既定ビュー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JSONView extends View {

	/**
	 * @access public
	 * @param Action $action 呼び出し元アクション
	 * @param string $suffix ビュー名サフィックス
	 * @param Renderer $renderer レンダラー
	 */
	public function __construct (Action $action, $suffix, Renderer $renderer = null) {
		parent::__construct($action, $suffix, $renderer);

		if ($status = $this->request->getAttribute('status')) {
			$this->setStatus($status);
		} else {
			if ($suffix == self::ERROR) {
				$this->setStatus(400);
			} else {
				$this->setStatus(200);
			}
		}
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @param int $flags フラグのビット列
	 *   MIMEUtils::WITHOUT_HEADER ヘッダを修正しない
	 *   MIMEUtils::WITH_HEADER ヘッダも修正
	 */
	public function setRenderer (Renderer $renderer, int $flags = MIMEUtils::WITH_HEADER) {
		if (!($renderer instanceof ResultJSONRenderer)) {
			$dest = new ResultJSONRenderer;
			if ($renderer instanceof JSONRenderer) {
				$dest->setContents(Tuple::create($renderer->getResult()));
			}
			$renderer = $dest;
		}
		if (!($renderer instanceof JSONRenderer)) {
			throw new ViewException(Utils::getClass($renderer) . 'をセットできません。');
		}
		parent::setRenderer($renderer, $flags);
	}

	/**
	 * レンダリング
	 *
	 * @access public
	 */
	public function render () {
		$params = $this->renderer->getParameters();
		$params['status'] = $this->getStatus();
		$params['module'] = $this->getModule()->getName();
		$params['action'] = $this->getAction()->getName();
		$params['params'] = $this->request->getParameters();
		if ($this->request->hasErrors()) {
			$params['errors'] = $this->request->getErrors();
		}
		parent::render();
	}
}
