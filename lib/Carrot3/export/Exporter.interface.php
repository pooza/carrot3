<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage export
 */

namespace Carrot3;

/**
 * エクスポータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface Exporter {

	/**
	 * 一時ファイルを返す
	 *
	 * @access public
	 * @return File 一時ファイル
	 */
	public function getFile ();

	/**
	 * レコードを追加
	 *
	 * @access public
	 * @param Tuple $record レコード
	 */
	public function addRecord (Tuple $record);

	/**
	 * タイトル行を設定
	 *
	 * @access public
	 * @param Tuple $row タイトル行
	 */
	public function setHeader (Tuple $row);
}
