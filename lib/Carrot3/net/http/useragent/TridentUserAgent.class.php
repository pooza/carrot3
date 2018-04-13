<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent
 */

namespace Carrot3;

/**
 * Tridentユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class TridentUserAgent extends UserAgent {
	const DEFAULT_NAME = 'Mozilla/5.0 (Trident/7.0; rv 11.0)';

	/**
	 * @access protected
	 * @param string $name ユーザーエージェント名
	 */
	protected function __construct (?string $name = null) {
		parent::__construct($name);
		$this->supports['html5_audio'] = !$this->isLegacy();
		$this->supports['html5_video'] = !$this->isLegacy();
	}

	/**
	 * ダウンロード用にエンコードされたファイル名を返す
	 *
	 * @access public
	 * @param string $name ファイル名
	 * @return string エンコード済みファイル名
	 */
	public function encodeFileName (string $name) {
		$name = URL::encode($name);
		$name = str_replace('+', ' ', $name);
		return StringUtils::sanitize($name);
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():string {
		if (!$this->digest) {
			$this->digest = Crypt::digest([
				__CLASS__,
				$this->isSmartPhone(),
				$this->isTablet(),
			]);
		}
		return $this->digest;
	}

	/**
	 * バージョンを返す
	 *
	 * IEとしてのバージョンを返す。
	 *
	 * @access public
	 * @return string バージョン
	 */
	public function getVersion () {
		if (!$this['version']) {
			if (mb_ereg('MSIE ([.[:digit:]]+);', $this->getName(), $matches)) {
				$this['version'] = $matches[1];
			} else if (mb_ereg('Trident', $this->getName(), $matches)) {
				if (mb_ereg('rv[ :]([.[:digit:]]+)', $this->getName(), $matches)) {
					$this['version'] = $matches[1];
				}
			}
		}
		return $this['version'];
	}

	/**
	 * レガシー環境/旧機種か？
	 *
	 * @access public
	 * @return bool レガシーならばTrue
	 */
	public function isLegacy ():bool {
		return $this->getVersion() < 11;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return '(MSIE|Trident)';
	}
}
