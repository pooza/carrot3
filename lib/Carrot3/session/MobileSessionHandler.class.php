<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session
 */

namespace Carrot3;

/**
 * ケータイ用セッションハンドラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MobileSessionHandler extends SessionHandler {

	/**
	 * @access public
	 */
	public function __construct () {
		session_write_close();
		ini_set('session.use_cookies', 0);
		ini_set('session.use_only_cookies', 0);
		ini_set('session.use_trans_sid', 0);
		ini_set('session.hash_function', 1);
		if (!$this->getStorage()->initialize()) {
			throw new SessionException('セッションを開始できません。');
		}
		session_start();
	}

	/**
	 * セッションIDを返す
	 *
	 * @access public
	 * @return integer セッションID
	 */
	public function getID () {
		if (!StringUtils::isBlank($id = $this->request[$this->getName()])) {
			session_id($id);
		}
		return parent::getID();
	}
}

