<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage user.role
 */

namespace Carrot3;

/**
 * rootロール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RootRole implements Role {
	use Singleton;
	private $email;

	/**
	 * ユーザーIDを返す
	 *
	 * @access public
	 * @return string ユーザーID
	 */
	public function getID () {
		return $this->getMailAddress()->getContents();
	}

	/**
	 * メールアドレスを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return MailAddress メールアドレス
	 */
	public function getMailAddress ($language = 'ja') {
		if (!$this->email) {
			$command = new CommandLine('hostname');
			$hostname = $command->getResult()[0];
			$this->email = MailAddress::create('root@' . $hostname, $this->getName($language));
		}
		return $this->email;
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string 名前
	 */
	public function getName ($language = 'ja') {
		return 'Charlie Root';
	}

	/**
	 * ユーザーIDを返す
	 *
	 * @access public
	 * @return string ユーザーID
	 */
	public function getUserID () {
		return $this->getMailAddress()->getContents();
	}

	/**
	 * 認証
	 *
	 * このロールは認証には使用しない。
	 *
	 * @access public
	 * @param string $password パスワード
	 * @return boolean 正しいユーザーならTrue
	 */
	public function auth ($password = null) {
		return false;
	}

	/**
	 * 認証時に与えられるクレデンシャルを返す
	 *
	 * @access public
	 * @return Tuple クレデンシャルの配列
	 */
	public function getCredentials () {
		return Tuple::create();
	}
}
