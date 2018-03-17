<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage memcache
 */

namespace Carrot3;

/**
 * memcacheサーバ
 *
 * PECL::memcachedのラッパー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Memcache implements \ArrayAccess {
	use BasicObject;
	protected $memcached;
	private $attributes;

	/**
	 * @access public
	 */
	public function __construct () {
		$this->memcached = new \Memcached;
		$this->attributes = Tuple::create();
	}

	/**
	 * 接続
	 *
	 * pconnectのエイリアス
	 *
	 * @access public
	 * @param mixed $host 接続先ホスト、又はUNIXソケット名
	 * @param int $port ポート、UNIXソケットの場合は0
	 * @return 接続の成否
	 */
	public function connect ($host, int $port) {
		return $this->pconnect($host, $port);
	}

	/**
	 * 持続接続
	 *
	 * @access public
	 * @param mixed $host 接続先ホスト、又はUNIXソケット名
	 * @param int $port ポート、UNIXソケットの場合は0
	 * @return 接続の成否
	 */
	public function pconnect ($host, int $port) {
		if (Numeric::isZero($port)) {
			$this->attributes['socket'] = $host;
			$this->attributes['connection_type'] = MemcacheManager::CONNECT_UNIX;
			$key = $host . ':11211'; //ポートは何故か0にならない。PECL::memcachedのバグ。
		} else {
			$this->attributes['connection_type'] = MemcacheManager::CONNECT_INET;
			if ($host instanceof Host) {
				$host = $host->getName();
			}
			$this->attributes['host'] = $host;
			$this->attributes['port'] = $port;
			$key = $host . $port;
		}

		if (!$this->memcached->addServer($host, $port)) {
			$this->attributes['error'] = true;
			return false;
		}
		$this->attributes->setParameters($this->memcached->getStats()[$key]);
		return true;
	}

	/**
	 * 属性値を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return mixed 属性値
	 */
	public function getAttribute ($name) {
		return $this->attributes[$name];
	}

	/**
	 * 属性を全て返す
	 *
	 * @access public
	 * @return Tuple 属性
	 */
	public function getAttributes () {
		return $this->attributes;
	}

	/**
	 * マネージャを返す
	 *
	 * @access public
	 * @return MemcacheManager マネージャ
	 */
	public function getManager () {
		return MemcacheManager::getInstance();
	}

	/**
	 * 接続タイプを返す
	 *
	 * @access public
	 * @return string 接続タイプ
	 *   MemcacheManager::CONNECT_UNIX UNIXソケット
	 *   MemcacheManager::CONNECT_INET TCP/IPソケット
	 */
	public function getConnectionType () {
		return $this->getAttribute('connection_type');
	}

	/**
	 * エントリーを取得
	 *
	 * @access public
	 * @param string $name キー
	 * @return string エントリーの値
	 */
	public function get ($name) {
		return $this->memcached->get($this->createKey($name));
	}

	/**
	 * エントリーを追加
	 *
	 * @access public
	 * @param string $name エントリー名
	 * @param string $value エントリーの値
	 * @param int $flag PECL::memcacheとの互換性の為の引数。未使用。
	 * @param int $expire 項目の有効期限。秒数又はタイムスタンプ。
	 * @return bool 処理の成否
	 */
	public function set ($name, $value, int $flag = null, int $expire = 0) {
		if ($value instanceof ParameterHolder) {
			$value = Tuple::create($value)->decode();
		} else if (is_object($value)) {
			throw new MemcacheException('オブジェクトを登録できません。');
		}
		return $this->memcached->set($this->createKey($name), $value, $expire);
	}

	/**
	 * エントリーを削除
	 *
	 * @access public
	 * @param string $name エントリー名
	 * @return bool 処理の成否
	 */
	public function delete ($name) {
		return $this->memcached->delete($this->createKey($name));
	}

	/**
	 * memcachedでのエントリー名を返す
	 *
	 * @access protected
	 * @param string $name エントリー名
	 * @return string memcachedでの属性名
	 */
	protected function createKey ($name) {
		return Crypt::digest([
			$this->controller->getHost()->getName(),
			Utils::getShortClass($this),
			$name,
		]);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return ($this->get($key) !== false);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->get($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed $value 要素
	 */
	public function offsetSet ($key, $value) {
		$this->set($key, $value);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		$this->delete($key);
	}

	/**
	 * 全て削除
	 *
	 * @access public
	 */
	public function clear () {
		$this->memcached->flush();
	}
}
