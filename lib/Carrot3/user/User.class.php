<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage user
 */

namespace Carrot3;

/**
 * ユーザー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class User extends ParameterHolder {
	use Singleton, BasicObject;
	protected $id;
	private $attributes;
	private $credentials;
	const COOKIE = 1;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->attributes = Tuple::create();
		$this->attributes->setParameters(filter_input_array(INPUT_COOKIE));
		$this->attributes->setParameters($this->getSession()['attributes']);

		$this->credentials = Tuple::create();
		$this->credentials->setParameters($this->getSession()['credentials']);

		$this->id = $this->getSession()[__CLASS__];
	}

	/**
	 * @access public
	 */
	public function __destruct () {
		$this->getSession()['attributes'] = $this->attributes;
		$this->getSession()['credentials'] = $this->credentials;
	}

	/**
	 * シングルトンインスタンスを返す
	 *
	 * @access public
	 * @return User インスタンス
	 * @static
	 */
	static public function getInstance () {
		if (!self::$instance) {
			if (StringUtils::isBlank(BS_USER_CLASS)) {
				self::$instance = new self;
			} else {
				$class = Loader::getInstance()->getClass(BS_USER_CLASS);
				self::$instance = new $class;
			}
		}
		return self::$instance;
	}

	/**
	 * 全ての属性を削除
	 *
	 * @access public
	 */
	public function clearAttributes () {
		$this->attributes->clear();
	}

	/**
	 * 属性値を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param int $flags フラグのビット列
	 *   self::COOKIE cookieのみ
	 * @return mixed 属性値
	 */
	public function getAttribute (string $name, int $flags = 0) {
		if ($flags & self::COOKIE) {
			return filter_input(INPUT_COOKIE, $name);
		}
		return $this->attributes[$name];
	}

	/**
	 * 属性値が存在するか？
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return bool 属性値が存在すればTrue
	 */
	public function hasAttribute (string $name):bool {
		return $this->attributes->hasParameter($name);
	}

	/**
	 * 属性値を設定
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param mixed $value 属性値
	 * @param Date $expire 期限
	 * @param string $domain 対象ドメイン
	 */
	public function setAttribute (string $name, $value, Date $expire = null, $domain = null) {
		$this->attributes[(string)$name] = $value;
		if ($expire) {
			if (StringUtils::isBlank($domain)) {
				$domain = $this->controller->getHost()->getName();
			}
			setcookie(
				(string)$name,
				$value,
				$expire->getTimestamp(),
				'/',
				$domain,
				$this->request->isTLS(), //セキュア属性
				true //httponly
			);
		}
	}

	/**
	 * 属性値を削除
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param string $domain Cookieの対象ドメイン
	 */
	public function removeAttribute (string $name, $domain = null) {
		$this->attributes->removeParameter($name);

		if (StringUtils::isBlank($domain)) {
			$domain = $this->controller->getHost()->getName();
		}
		$expire = Date::create();
		$expire['hour'] = '-1';
		setcookie($name, null, $expire->getTimestamp(), '/', $domain);
	}

	/**
	 * 属性値を全て返す
	 *
	 * @access public
	 * @return Tuple 属性値
	 */
	public function getAttributes () {
		return $this->attributes;
	}

	/**
	 * 属性値をまとめて設定
	 *
	 * @access public
	 * @param iterable $attributes 属性値
	 */
	public function setAttributes (iterable $attributes) {
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}

	/**
	 * セッションを返す
	 *
	 * @access protected
	 * @return BSSession セッション
	 */
	protected function getSession () {
		return $this->request->getSession();
	}

	/**
	 * ユーザーIDを返す
	 *
	 * @access public
	 * @return string ユーザーID
	 */
	public function getID () {
		return $this->id;
	}

	/**
	 * ログイン
	 *
	 * @access public
	 * @param UserIdentifier $id ユーザーIDを含んだオブジェクト
	 * @param string $password パスワード
	 * @return bool 成功ならTrue
	 */
	public function login (UserIdentifier $identifier = null, $password = null) {
		if ($identifier && $identifier->auth($password)) {
			$this->pass($identifier);
			return true;
		}
		return false;
	}

	/**
	 * 認証をパスする
	 *
	 * @access public
	 * @param UserIdentifier $id ユーザーIDを含んだオブジェクト
	 */
	public function pass (UserIdentifier $identifier) {
		$this->id = $identifier->getID();
		$this->getSession()[__CLASS__] = $this->id;
		$this->getSession()->refresh();
		foreach ($identifier->getCredentials() as $credential) {
			$this->allow($credential);
		}
	}

	/**
	 * ログアウト
	 *
	 * @access public
	 */
	public function logout () {
		$this->id = null;
		$this->revokeAll();
		$this->getSession()[__CLASS__] = null;
		$this->getSession()->refresh();
	}

	/**
	 * 全てのクレデンシャルを返す
	 *
	 * @access public
	 * @return Tuple 全てのクレデンシャル
	 */
	public function getCredentials () {
		return $this->credentials;
	}

	/**
	 * クレデンシャルを追加
	 *
	 * @access public
	 * @param string $credential クレデンシャル
	 */
	public function allow ($credential) {
		$this->credentials[$credential] = true;
	}

	/**
	 * クレデンシャルを削除
	 *
	 * @access public
	 * @param string $credential クレデンシャル
	 */
	public function revoke ($credential) {
		$this->credentials[$credential] = false;
	}

	/**
	 * 全てのクレデンシャルを削除
	 *
	 * @access public
	 */
	public function revokeAll () {
		$this->credentials->clear();
	}

	/**
	 * クレデンシャルを持っているか？
	 *
	 * @access public
	 * @param string $name クレデンシャル名
	 * @return bool 持っていればTrue
	 */
	public function hasCredential (?string $name):bool {
		return StringUtils::isBlank($name) || $this->credentials[$name];
	}

	/**
	 * 管理者権限を持っているか？
	 *
	 * @access public
	 * @return bool 持っていればTrue
	 */
	public function isAdministrator ():bool {
		return $this->hasCredential(AdministratorRole::CREDENTIAL);
	}

	/**
	 * 発行者権限を持っているか？
	 *
	 * @access public
	 * @return bool 持っていればTrue
	 */
	public function isAuthor ():bool {
		return $this->hasCredential(AuthorRole::CREDENTIAL);
	}

	/**
	 * ゲストユーザーか？
	 *
	 * @access public
	 * @return bool ゲストユーザーならばTrue
	 */
	public function isGuest ():bool {
		foreach ($this->getCredentials() as $credential) {
			if (!!$credential) {
				return false;
			}
		}
		return true;
	}
}
