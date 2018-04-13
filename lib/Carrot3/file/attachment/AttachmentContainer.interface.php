<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage image.attachment
 */

namespace Carrot3;

/**
 * 添付ファイルコンテナ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface AttachmentContainer {

	/**
	 * 添付ファイルを返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return File 添付ファイル
	 */
	public function getAttachment (string $name):?File;

	/**
	 * 添付ファイルのダウンロード時の名を返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return string ダウンロード時ファイル名
	 */
	public function getAttachmentFileName (string $name);
}
