<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage serialize.storage.database
 */

namespace Carrot3;

/**
 * ストアドシリアライズレコード
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SerializeEntry extends Record {

	/**
	 * 更新可能か？
	 *
	 * @access protected
	 * @return bool 更新可能ならTrue
	 */
	protected function isUpdatable () {
		return true;
	}

	/**
	 * 更新
	 *
	 * @access public
	 * @param mixed $values 更新する値
	 * @param int $flags フラグのビット列
	 *   Database::WITHOUT_LOGGING ログを残さない
	 */
	public function update ($values, $flags = Database::WITHOUT_LOGGING) {
		parent::update($values, $flags);
	}

	/**
	 * 削除可能か？
	 *
	 * @access protected
	 * @return bool 削除可能ならTrue
	 */
	protected function isDeletable () {
		return true;
	}

	/**
	 * シリアライズするか？
	 *
	 * @access public
	 * @return bool シリアライズするならTrue
	 * @final
	 */
	final function isSerializable () {
		return false;
	}
}
