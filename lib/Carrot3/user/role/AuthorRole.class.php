<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage user.role
 */

namespace Carrot3;

/**
 * 発行者ロール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class AuthorRole implements Role {
	use Singleton, BasicObject;
	protected $credentials;
	protected $twitterAccount;
	const CREDENTIAL = 'Author';

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
	 * @param string $lang 言語
	 * @return MailAddress メールアドレス
	 */
	public function getMailAddress (?string $lang = 'ja') {
		return MailAddress::create(BS_AUTHOR_EMAIL, $this->getName($lang));
	}

	/**
	 * Twitterアカウントを返す
	 *
	 * @access public
	 * @return TwitterAccount アカウント
	 */
	public function getTwitterAccount () {
		if (!$this->twitterAccount && !StringUtils::isBlank(BS_AUTHOR_TWITTER)) {
			$this->twitterAccount = new TwitterAccount(BS_AUTHOR_TWITTER);
		}
		return $this->twitterAccount;
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @param string $lang 言語
	 * @return string 名前
	 */
	public function getName (?string $lang = 'ja') {
		if (StringUtils::isBlank($name = BS_AUTHOR_NAME)) {
			$name = (new ConstantHandler)['APP_NAME_' . $lang];
		}
		return $name;
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
	 * @access public
	 * @param string $password パスワード
	 * @return bool 正しいユーザーならTrue
	 */
	public function auth ($password = null) {
		return Crypt::getInstance()->auth(BS_AUTHOR_PASSWORD, $password);
	}

	/**
	 * 認証時に与えられるクレデンシャルを返す
	 *
	 * @access public
	 * @return Tuple クレデンシャルの配列
	 */
	public function getCredentials () {
		if (!$this->credentials) {
			$this->credentials = Tuple::create([self::CREDENTIAL]);
		}
		return $this->credentials;
	}
}
