<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage
 */

namespace Carrot3;

/**
 * シリアライズストレージ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class SerializeStorage {
	use BasicObject;
	protected $handler;

	/**
	 * 初期化
	 *
	 * @access public
	 * @param SerializeHandler $handler
	 * @return bool
	 */
	public function initialize (SerializeHandler $handler):bool {
		$this->handler = $handler;
		return true;
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 * @abstract
	 */
	abstract public function setAttribute (string $name, $value);

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @abstract
	 */
	abstract public function removeAttribute (string $name);

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param Date $date 比較する日付 - この日付より古い属性値は破棄
	 * @return mixed 属性値
	 * @abstract
	 */
	abstract public function getAttribute (string $name, Date $date = null);

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 * @abstract
	 */
	abstract public function getUpdateDate (string $name):?Date;

	/**
	 * クリア
	 *
	 * @access public
	 * @abstract
	 */
	abstract public function clear ();

	/**
	 * シリアライザを返す
	 *
	 * @access public
	 * @return Serializer
	 */
	protected function getSerializer ():Serializer {
		if (!$this->handler) {
			throw new SerializeException($this . 'が初期化されていません。');
		}
		return $this->handler->getSerializer();
	}
}
