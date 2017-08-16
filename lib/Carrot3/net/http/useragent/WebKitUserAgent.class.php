<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * Webkitユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class WebKitUserAgent extends UserAgent {
	const DEFAULT_NAME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36';
	const ACCESSOR = 'force_webkit';

	/**
	 * @access protected
	 * @param string $name ユーザーエージェント名
	 */
	protected function __construct ($name = null) {
		parent::__construct($name);
		$this['is_chrome'] = $this->isChrome();
		$this['is_edge'] = $this->isEdge();
		$this['is_safari'] = $this->isSafari();
		$this->supports['html5_video'] = true;
		$this->supports['html5_audio'] = true;
		$this->supports['flash'] = true;
		$this->supports['cookie'] = true;
		$this->supports['attach_file'] = true;
	}

	/**
	 * Google Chromeか？
	 *
	 * @access public
	 * @return boolean Google ChromeならTrue
	 */
	public function isChrome () {
		return StringUtils::isContain('Chrome', $this->getName()) && !$this->isEdge();
	}

	/**
	 * Edgeか？
	 *
	 * @access public
	 * @return boolean Google ChromeならTrue
	 */
	public function isEdge () {
		return StringUtils::isContain('Edge', $this->getName());
	}

	/**
	 * Safariか？
	 *
	 * @access public
	 * @return boolean SafariならTrue
	 */
	public function isSafari () {
		return StringUtils::isContain('Macintosh', $this->getName()) && !$this->isChrome();
	}

	/**
	 * ダウンロード用にエンコードされたファイル名を返す
	 *
	 * @access public
	 * @param string $name ファイル名
	 * @return string エンコード済みファイル名
	 */
	public function encodeFileName ($name) {
		if ($this->isChrome()) {
			return rawurlencode($name);
		} else {
			return addslashes($name);
		}
	}

	/**
	 * バージョンを返す
	 *
	 * @access public
	 * @return string バージョン
	 */
	public function getVersion () {
		if (!$this['version']) {
			if (mb_ereg('AppleWebKit/([.[:digit:]]+)', $this->getName(), $matches)) {
				$this['version'] = $matches[1];
			}
		}
		return $this['version'];
	}

	/**
	 * レガシー環境/旧機種か？
	 *
	 * @access public
	 * @return boolean レガシーならばTrue
	 */
	public function isLegacy () {
		return version_compare($this->getVersion(), '100.0', '<'); // Safari 1.1未満
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return 'AppleWebKit';
	}
}

