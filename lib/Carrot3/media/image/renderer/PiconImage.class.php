<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image.renderer
 */

namespace Carrot3;

/**
 * Picon画像レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PiconImage extends Image {
	protected $url;
	protected $service;
	protected $method;

	/**
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 */
	public function __construct (ParameterHolder $params = null) {
		if ($params) {
			if ($params['file'] && ($params['file'] instanceof \Carrot3\ImageFile)) {
				$this->setImage($params['file']->getContents());
			}
			if ($params['url']) {
				$this->setURL(URL::create($params['url']));
			}
			if ($params['method']) {
				$this->setResizeMethod($params['method']);
			}
		}
	}

	/**
	 * リサイズ関数を設定
	 *
	 * @access public
	 * @param string $function 関数名
	 */
	public function setResizeMethod ($method) {
		$this->method = $method;
	}

	/**
	 * サイズ変更
	 *
	 * @access public
	 * @param integer $width 幅
	 * @param integer $height 高さ
	 */
	public function resize ($width, $height) {
		$this->getService()->resize($this, $width, $height);
	}

	/**
	 * 幅変更
	 *
	 * @access public
	 * @param integer $width 幅
	 */
	public function resizeWidth ($width) {
		if ($this->getWidth() < $width) {
			return;
		}
		$this->getService()->resizeWidth($this, $width, $this->method);
	}

	/**
	 * URLを返す
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL () {
		return $this->url;
	}

	/**
	 * URLを設定
	 *
	 * @access public
	 * @param HTTPRedirector $url
	 */
	public function setURL (HTTPRedirector $url) {
		$this->url = $url->getURL();
	}

	/**
	 * piconサービスを返す
	 *
	 * @access protected
	 * @return PiconService
	 */
	protected function getService () {
		if (!$this->service && $this->url) {
			$this->service = new PiconService($this->url['host'], $this->url['port']);
		}
		return $this->service;
	}
}

