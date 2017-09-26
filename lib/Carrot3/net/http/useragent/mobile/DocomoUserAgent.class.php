<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent.mobile
 */

namespace Carrot3;

/**
 * Docomoユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class DocomoUserAgent extends MobileUserAgent {
	const DEFAULT_NAME = 'DoCoMo/2.0 (c500;)';

	/**
	 * @access protected
	 * @param string $name ユーザーエージェント名
	 */
	protected function __construct ($name = null) {
		if (StringUtils::isBlank($name)) {
			$name = self::DEFAULT_NAME;
		}
		parent::__construct($name);
		$this['is_foma'] = $this->isFOMA();
		$this['version'] = $this->getVersion();
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return 'DoCoMo';
	}

	/**
	 * FOMA端末か？
	 *
	 * @access public
	 * @return boolean FOMA端末ならばTrue
	 */
	public function isFOMA () {
		return !mb_ereg('DoCoMo/1\\.0', $this->getName());
	}

	/**
	 * 規定の画像形式を返す
	 *
	 * @access public
	 * @return string 規定の画像形式
	 */
	public function getDefaultImageType () {
		return 'image/jpeg';
	}

	/**
	 * バージョンを返す
	 *
	 * iモードブラウザのバージョン
	 *
	 * @access public
	 * @return string バージョン
	 */
	public function getVersion () {
		if (!$this['version']) {
			if (mb_ereg('[/(]c([[:digit:]]+)[;/]', $this->getName(), $matches)) {
				if ($matches[1] < 500) {
					$this['version'] = 1;
				} else {
					$this['version'] = 2;
				}
			}
		}
		return $this['version'];
	}

	/**
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		$info = Tuple::create();
		if (1 < $this->getVersion()) {
			$info['width'] = BS_IMAGE_MOBILE_SIZE_VGA_WIDTH;
			$info['height'] = BS_IMAGE_MOBILE_SIZE_VGA_HEIGHT;
		} else {
			$info['width'] = BS_IMAGE_MOBILE_SIZE_QVGA_WIDTH;
			$info['height'] = BS_IMAGE_MOBILE_SIZE_QVGA_HEIGHT;
		}
		return $info;
	}
}

