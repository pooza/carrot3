<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent.smartphone
 */

namespace Carrot3;

/**
 * Androidユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AndroidUserAgent extends BlinkUserAgent {

	/**
	 * @access protected
	 * @param string $name ユーザーエージェント名
	 */
	protected function __construct (?string $name = null) {
		parent::__construct($name);
		$this->supports['html5_audio'] = version_compare('534.30', $this->getVersion(), '<');
		$this->supports['html5_video'] = version_compare('534.30', $this->getVersion(), '<');
		$this->supports['touch'] = true;
		$this->supports['html5_force_datepicker'] = true;
	}

	/**
	 * スマートフォンか？
	 *
	 * @access public
	 * @return bool スマートフォンならTrue
	 * @link http://googlewebmastercentral-ja.blogspot.com/2011/05/android.html
	 */
	public function isSmartPhone ():bool {
		return StringUtils::isContain('Mobile', $this->getName());
	}

	/**
	 * タブレット型か？
	 *
	 * @access public
	 * @return bool タブレット型ならTrue
	 */
	public function isTablet ():bool {
		return !$this->isSmartPhone();
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
		return 'Android';
	}
}
