<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize
 */

namespace Carrot3;

/**
 * シリアライズ可能なオブジェクトへのインターフェース
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface Serializable {

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():string;

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return mixed シリアライズ時の値
	 */
	public function getSerialized ();

	/**
	 * シリアライズされたキャッシュを削除
	 *
	 * @access public
	 */
	public function removeSerialized ();

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize ();

	/**
	 * @access public
	 */
	public function __toString ();
}
