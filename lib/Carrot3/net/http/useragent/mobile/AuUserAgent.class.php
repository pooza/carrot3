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
	protected function __construct ($name = null) {
		if (StringUtils::isBlank($name)) {
			$name = self::DEFAULT_NAME;
		}
		parent::__construct($name);
		$this->bugs['multipart_form'] = true;
		$this->supports['image_copyright'] = true;
		$this['is_wap2'] = $this->isWAP2();
	}

	/**
	 * WAP2.0端末か？
	 *
	 * @access public
	 * @return boolean WAP2.0端末ならばTrue
	 */
	public function isWAP2 () {
		return mb_ereg('^KDDI', $this->getName());
	}

	/**
	 * 旧機種か？
	 *
	 * @access public
	 * @return boolean 旧機種ならばTrue
	 */
	public function isLegacy () {
		if (BS_USERAGENT_MOBILE_DENY_ON_HTTPS && $this->request->isSSL()) {
			return true;
		}
		return !$this->isWAP2();
	}

	/**
	 * 画面情報を返す
	 *
	 * @access public
	 * @return Tuple 画面情報
	 */
	public function getDisplayInfo () {
		$info = $this->controller->getAttribute('X-UP-DEVCAP-SCREENPIXELS');
		if (StringUtils::isBlank($info)) {
			return parent::getDisplayInfo();
		}
		$info = StringUtils::explode(',', $info);

		return Tuple::create([
			'width' => (int)$info[0],
			'height' => (int)$info[1],
		]);
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

