<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.http.useragent.mobile
 */

namespace Carrot3;

/**
 * Auユーザーエージェント
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AuUserAgent extends MobileUserAgent {
	const DEFAULT_NAME = 'KDDI';

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
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		if ($info = $this->controller->getAttribute('X-UP-DEVCAP-SCREENPIXELS')) {
			$info = StringUtils::explode(',', $info);
			return Tuple::create([
				'width' => (int)$info[0],
				'height' => (int)$info[1],
			]);
		}
		return parent::getDisplayInfo();
	}

	/**
	 * 一致すべきパターンを返す
	 *
	 * @access public
	 * @return string パターン
	 */
	public function getPattern () {
		return '^(UP\\.Browser|KDDI)';
	}
}
