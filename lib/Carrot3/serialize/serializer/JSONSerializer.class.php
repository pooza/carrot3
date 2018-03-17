<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.serializer
 */

namespace Carrot3;

/**
 * JSONシリアライザー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JSONSerializer implements Serializer {

	/**
	 * 初期化
	 *
	 * @access public
	 * @return string 利用可能ならTrue
	 */
	public function initialize () {
		return extension_loaded('json');
	}

	/**
	 * シリアライズされた文字列を返す
	 *
	 * @access public
	 * @param mixed $value 対象
	 * @param int $flags フラグのビット列
	 * @return string シリアライズされた文字列
	 */
	public function encode ($value, int $flags = 0) {
		$value = StringUtils::convertEncoding($value, 'utf-8');
		if (is_iterable($value)) {
			$value = Tuple::create($value);
			$value = $value->decode();
		}
		return json_encode(
			$value,
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | $flags
		);
	}

	/**
	 * シリアライズされた文字列を元に戻す
	 *
	 * @access public
	 * @param string $value 対象
	 * @return mixed もとの値
	 */
	public function decode ($value) {
		return json_decode($value, true);
	}

	/**
	 * サフィックスを返す
	 *
	 * @access public
	 * @return string サフィックス
	 */
	public function getSuffix () {
		return '.json';
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return 'JSONシリアライザー';
	}
}
