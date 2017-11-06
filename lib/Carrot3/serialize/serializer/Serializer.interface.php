<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.serializer
 */

namespace Carrot3;

/**
 * シリアライザー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface Serializer {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize ();

	/**
	 * シリアライズされた文字列を返す
	 *
	 * @access public
	 * @param mixed $value 対象
	 * @return string シリアライズされた文字列
	 */
	public function encode ($value);

	/**
	 * シリアライズされた文字列を元に戻す
	 *
	 * @access public
	 * @param string $value 対象
	 * @return mixed もとの値
	 */
	public function decode ($value);

	/**
	 * サフィックスを返す
	 *
	 * @access public
	 * @return string サフィックス
	 */
	public function getSuffix ();
}
