<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media.image
 */

namespace Carrot3;

/**
 * 画像コンテナ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface ImageContainer {

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache (string $size);

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 * @return Tuple 画像の情報
	 */
	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0);

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile (string $size);

	/**
	 * 画像ファイルベース名を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return string 画像ファイルベース名
	 */
	public function getImageFileBaseName (string $size);

	/**
	 * コンテナのIDを返す
	 *
	 * コンテナを一意に識別する値。
	 * ファイルならinode、DBレコードなら主キー。
	 *
	 * @access public
	 * @return int ID
	 */
	public function getID ();

	/**
	 * コンテナの名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName ();

	/**
	 * コンテナのラベルを返す
	 *
	 * @access public
	 * @param string $lang 言語
	 * @return string ラベル
	 */
	public function getLabel (?string $lang = 'ja');
}
