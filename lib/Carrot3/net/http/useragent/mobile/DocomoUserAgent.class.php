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
	protected function __construct (?string $name = null) {
		if (StringUtils::isBlank($name)) {
			$name = self::DEFAULT_NAME;
		}
		parent::__construct($name);
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
	 * 規定の画像形式を返す
	 *
	 * @access public
	 * @return string 規定の画像形式
	 */
	public function getDefaultImageType () {
		return 'image/jpeg';
	}

	/**
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		return Tuple::create([
			'width' => BS_IMAGE_MOBILE_SIZE_QVGA_WIDTH,
			'height' => BS_IMAGE_MOBILE_SIZE_QVGA_HEIGHT,
		]);
	}
}
