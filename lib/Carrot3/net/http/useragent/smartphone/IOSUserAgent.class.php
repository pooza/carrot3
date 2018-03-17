<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent.smartphone
 */

namespace Carrot3;

/**
 * iOSユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class IOSUserAgent extends WebKitUserAgent {

	/**
	 * @access protected
	 * @param string $name ユーザーエージェント名
	 */
	protected function __construct ($name = null) {
		parent::__construct($name);
		$this->supports['touch'] = true;
		$this->supports['html5_force_datepicker'] = true;
	}

	/**
	 * スマートフォンか？
	 *
	 * @access public
	 * @return bool スマートフォンならTrue
	 */
	public function isSmartPhone () {
		return !$this->isTablet();
	}

	/**
	 * タブレット型か？
	 *
	 * @access public
	 * @return bool タブレット型ならTrue
	 */
	public function isTablet () {
		return StringUtils::isContain('iPad', $this->getName());
	}

	/**
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		$info = Tuple::create();
		if ($this->isSmartPhone()) {
			$info['width'] = BS_VIEW_LAYOUT_SMARTPHONE_WIDTH;
		}
		return $info;
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return 'i(Phone|Pod|Pad);';
	}
}
