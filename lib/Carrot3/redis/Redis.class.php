<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage redis
 */

namespace Carrot3;

/**
 * Redis
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Redis extends \Redis implements \ArrayAccess {
	use KeyGenerator;
	protected $serializer;

	/**
	 * @access public
	 */
	public function __construct () {
		if (!$this->connect(BS_REDIS_HOST, BS_REDIS_PORT)) {
			$url = URL::create(null, 'tcp');
			$url['host'] = BS_REDIS_HOST;
			$url['port'] = BS_REDIS_PORT;
			throw new RedisException($url->getContents() . 'に接続できません。');
		}
		$this->setSerializer(new PHPSerializer);
	}

	/**
	 * @access public
	 * @param int $id サーバ番号
	 * @return bool 成否
	 */
	public function select ($id) {
		if (!parent::select($id)) {
			throw new RedisException($id . 'に接続できません。');
		}
		return true;
	}

	/**
	 * シリアライザーを返す
	 *
	 * @access public
	 * @param Serializer $serializer シリアライザー
	 */
	public function setSerializer (Serializer $serializer) {
		$this->serializer = $serializer;
	}

	/**
	 * 属性値を全て返す
	 *
	 * @access public
	 * @return Tuple 属性値
	 */
	public function getAttributes ():Tuple {
		return Tuple::create($this->info());
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return $this->exists($this->createKey($key));
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->serializer->decode(
			$this->get($this->createKey($key))
		);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed $value 要素
	 */
	public function offsetSet ($key, $value) {
		$this->set(
			$this->createKey($key),
			$this->serializer->encode($value)
		);
	}

	public function setEx ($key, $ttl, $value) {
		parent::setEx(
			$this->createKey($key),
			$ttl,
			$this->serializer->encode($value)
		);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		$this->delete($this->createKey($key));
	}

	/**
	 * クリア
	 *
	 * @access public
	 */
	public function clear () {
		return $this->flushDb();
	}
}
