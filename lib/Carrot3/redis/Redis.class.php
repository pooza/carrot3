<?php
namespace Carrot3;

class Redis extends \Redis implements \ArrayAccess {
	use KeyGenerator;
	protected $serializer;

	public function __construct () {
		if (!$this->connect(BS_REDIS_HOST, BS_REDIS_PORT)) {
			$url = URL::create(null, 'tcp');
			$url['host'] = BS_REDIS_HOST;
			$url['port'] = BS_REDIS_PORT;
			throw new RedisException($url->getContents() . 'に接続できません。');
		}
		$this->setSerializer(new PHPSerializer);
	}

	public function select ($id) {
		if (!parent::select($id)) {
			throw new RedisException($id . 'に接続できません。');
		}
		return true;
	}

	public function setSerializer (Serializer $serializer) {
		$this->serializer = $serializer;
	}

	public function getAttributes ():Tuple {
		return Tuple::create($this->info());
	}

	public function offsetExists ($key) {
		return $this->exists($this->createKey($key));
	}

	public function offsetGet ($key) {
		return $this->serializer->decode(
			$this->get($this->createKey($key))
		);
	}

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

	public function offsetUnset ($key) {
		$this->delete($this->createKey($key));
	}

	public function clear () {
		return $this->flushDb();
	}
}
