<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent.mobile
 */

namespace Carrot3;

/**
 * モバイルユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class MobileUserAgent extends UserAgent {
	use KeyGenerator;

	/**
	 * ビューを初期化
	 *
	 * @access public
	 * @param SmartyView 対象ビュー
	 * @return bool
	 */
	public function initializeView (SmartyView $view):bool {
		parent::initializeView($view);
		$view->getRenderer()->addModifier('sanitize');
		$view->getRenderer()->addOutputFilter('mobile');
		$view->getRenderer()->addOutputFilter('encoding');
		$view->getRenderer()->addOutputFilter('trim');
		$view->getRenderer()->addOutputFilter('strip_comment');
		return true;
	}

	/**
	 * ケータイ環境か？
	 *
	 * @access public
	 * @return bool ケータイ環境ならTrue
	 */
	public function isMobile ():bool {
		return true;
	}

	/**
	 * 旧機種か？
	 *
	 * @access public
	 * @return bool 旧機種ならばTrue
	 */
	public function isLegacy ():bool {
		return true;
	}

	/**
	 * 再生可能か？
	 *
	 * @access public
	 * @param MediaFile $file
	 * @return bool 再生できるならTrue
	 */
	public function isPlayable (MediaFile $file):bool {
		return false;
	}

	/**
	 * キャリア名を返す
	 *
	 * @access public
	 * @return string キャリア名
	 */
	public function getCarrier () {
		if (mb_ereg('^\\\\([[:alnum:]]+)UserAgent$', Utils::getShortClass($this), $matches)) {
			return $matches[1];
		}
	}

	/**
	 * 規定の画像形式を返す
	 *
	 * @access public
	 * @return string 規定の画像形式
	 */
	public function getDefaultImageType () {
		return 'image/png';
	}

	/**
	 * 規定のエンコードを返す
	 *
	 * @access public
	 * @return string 規定のエンコード
	 */
	public function getDefaultEncoding () {
		return 'sjis-win';
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():?string {
		return $this->createKey([
			$this->getDisplayInfo()['width'],
		]);
	}
}
