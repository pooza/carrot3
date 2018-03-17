<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize
 */

namespace Carrot3;

/**
 * シリアライズされたキャッシュ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SerializeHandler implements \ArrayAccess {
	use BasicObject;
	private $serializer;
	private $storage;
	private $attributes;

	/**
	 * @access public
	 */
	public function __construct (SerializeStorage $storage = null, Serializer $serializer = null) {
		if (!$serializer) {
			$serializer = $this->loader->createObject(BS_SERIALIZE_SERIALIZER . 'Serializer');
		}
		$this->serializer = $serializer;
		if (!$this->serializer->initialize()) {
			throw new ConfigException($serializer . 'が初期化できません。');
		}

		if (!$storage) {
			$storage = $this->loader->createObject(BS_SERIALIZE_STORAGE . 'SerializeStorage');
		}
		$this->storage = $storage;
		if (!$this->storage->initialize()) {
			throw new ConfigException($storage . 'が初期化できません。');
		}
	}

	/**
	 * シリアライザーを返す
	 *
	 * @access public
	 * @return Serializer シリアライザー
	 */
	public function getSerializer () {
		return $this->serializer;
	}

	/**
	 * ストレージを返す
	 *
	 * @access public
	 * @return SerializeStorage ストレージ
	 */
	public function getStorage () {
		return $this->storage;
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param Date $date 比較する日付 - この日付より古い属性値は破棄
	 * @return mixed 属性値
	 */
	public function getAttribute ($name, Date $date = null) {
		return $this->storage->getAttribute($this->createKey($name), $date);
	}

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 */
	public function getUpdateDate ($name) {
		return $this->storage->getUpdateDate($this->createKey($name));
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 */
	public function setAttribute ($name, $value) {
		if (is_array($value) || ($value instanceof ParameterHolder)) {
			$value = Tuple::create($value)->decode();
		}
		$this->storage->setAttribute($this->createKey($name), $value);
	}

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function removeAttribute ($name) {
		$this->storage->removeAttribute($this->createKey($name));
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return ($this->getAttribute($key) !== null);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->getAttribute($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed $value 要素
	 */
	public function offsetSet ($key, $value) {
		$this->setAttribute($key, $value);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		$this->removeAttribute($key);
	}

	/**
	 * シリアライズのダイジェストを返す
	 *
	 * @access public
	 * @param mixed $name 属性名に用いる値
	 * @return string 属性名
	 */
	public function createKey ($name) {
		if ($name instanceof Serializable) {
			return $name->digest();
		} else if (is_object($name)) {
			return Crypt::digest(Utils::getClass($name));
		}
		return (string)$name;
	}

	/**
	 * シリアライズ可能なクラスを返す
	 *
	 * @access public
	 * @return Tuple クラス名の配列
	 * @static
	 */
	static public function getClasses () {
		if (StringUtils::isBlank(BS_SERIALIZE_CLASSES)) {
			return Tuple::create();
		} else {
			return StringUtils::explode(',', BS_SERIALIZE_CLASSES);
		}
	}
}
