<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage user.role
 */

namespace Carrot3;

/**
 * ロール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
interface Role extends UserIdentifier {

	/**
	 * メールアドレスを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return MailAddress メールアドレス
	 */
	public function getMailAddress ($language = 'ja');

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string 名前
	 */
	public function getName ($language = 'ja');
}
