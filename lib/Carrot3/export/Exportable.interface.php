<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage export
 */

namespace Carrot3;

/**
 * エクスポート可能
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface Exportable {

	/**
	 * エクスポータを返す
	 *
	 * @access public
	 * @return Exporter エクスポータ
	 */
	public function getExporter ();

	/**
	 * 見出しを返す
	 *
	 * @access public
	 * @return Tuple 見出し
	 */
	public function getHeader ();

	/**
	 * エクスポート
	 *
	 * @access public
	 * @return Exporter エクスポーター
	 */
	public function export ();
}
