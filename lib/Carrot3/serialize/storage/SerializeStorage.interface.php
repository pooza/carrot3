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
 */
interface SerializeStorage {

	/**
	 * @access public
	 * @param Serializer $serializer
	 */
	public function __construct (Serializer $serializer = null);

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize ();

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param mixed $value 値
	 * @return string シリアライズされた値
	 */
	public function setAttribute (string $name, $value);

	/**
	 * 属性を削除
	 *
	 * @access public
	 * @param string $name 属性の名前
	 */
	public function removeAttribute (string $name);

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @param Date $date 比較する日付 - この日付より古い属性値は破棄
	 * @return mixed 属性値
	 */
	public function getAttribute (string $name, Date $date = null);

	/**
	 * 属性の更新日を返す
	 *
	 * @access public
	 * @param string $name 属性の名前
	 * @return Date 更新日
	 */
	public function getUpdateDate (string $name);
}
