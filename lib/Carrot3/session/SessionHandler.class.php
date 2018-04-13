<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage session
 */

namespace Carrot3;

/**
 * セッションハンドラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SessionHandler implements \ArrayAccess, Assignable {
	use BasicObject;
	private $storage;
	protected $directory;

	/**
	 * @access public
	 */
	public function __construct () {
		ini_set('session.use_cookies', 1);
		ini_set('session.cookie_httponly', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.use_strict_mode', 1);
		if (headers_sent() || !$this->getStorage()->initialize()) {
			throw new SessionException('セッションを開始できません。');
		}
		session_start();
	}

	/**
	 * セッションIDを返す
	 *
	 * @access public
	 * @return int セッションID
	 */
	public function getID () {
		return session_id();
	}

	/**
	 * セッション名を返す
	 *
	 * @access public
	 * @return int セッション名
	 */
	public function getName () {
		return session_name();
	}

	/**
	 * セッションIDを再生成
	 *
	 * @access public
	 */
	public function refresh () {
		session_regenerate_id(true);
	}

	/**
	 * セッションストレージを返す
	 *
	 * @access protected
	 * @return SessionStorage セッションストレージ
	 */
	protected function getStorage () {
		if (!$this->storage) {
			$this->storage = $this->loader->createObject(
				BS_SESSION_STORAGE . 'SessionStorage'
			);
		}
		return $this->storage;
	}

	/**
	 * セッション変数を返す
	 *
	 * @access public
	 * @param string $key 変数名
	 * @return mixed セッション変数
	 */
	public function read ($key) {
		if (isset($_SESSION[$key])) {
			return $_SESSION[$key];
		}
	}

	/**
	 * セッション変数を書き込む
	 *
	 * @access public
	 * @param string $key 変数名
	 * @param mixed $value 値
	 */
	public function write ($key, $value) {
		if (is_iterable($value)) {
			$value = Tuple::create($value)->decode();
		}
		$_SESSION[$key] = $value;
	}

	/**
	 * セッション変数を削除
	 *
	 * @access public
	 * @param string $key 変数名
	 */
	public function remove ($key) {
		if (isset($_SESSION[$key])) {
			unset($_SESSION[$key]);
		}
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return ($this[$key] !== null);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->read($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed 要素
	 */
	public function offsetSet ($key, $value) {
		$this->write($key, $value);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		$this->remove($key);
	}

	/**
	 * セッションディレクトリを返す
	 *
	 * @access public
	 * @return Directory セッションディレクトリ
	 */
	public function getDirectory () {
		if (!$this->directory) {
			if (!$this->directory = FileUtils::getDirectory('tmp')->getEntry($this->getID())) {
				$this->directory = FileUtils::getDirectory('tmp')->createDirectory($this->getID());
			}
		}
		return $this->directory;
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return Tuple::create([
			'name' => $this->getName(),
			'id' => $this->getID(),
		]);
	}
}
