<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed
 */

namespace Carrot3;

/**
 * フィード文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface FeedDocument {

	/**
	 * エントリー要素の名前を返す
	 *
	 * @access public
	 * @return string
	 */
	public function getEntryElementName ();

	/**
	 * エントリー要素要素の格納先を返す
	 *
	 * @access public
	 * @return XMLElement
	 */
	public function getEntryRootElement ();

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle ();

	/**
	 * タイトルを設定
	 *
	 * @access public
	 * @param string $title タイトル
	 */
	public function setTitle ($title);

	/**
	 * ディスクリプションを設定
	 *
	 * @access public
	 * @param string $description ディスクリプション
	 */
	public function setDescription ($description);

	/**
	 * リンクを返す
	 *
	 * @access public
	 * @return HTTPURL リンク
	 */
	public function getLink ();

	/**
	 * リンクを設定
	 *
	 * @access public
	 * @param HTTPRedirector $link リンク
	 */
	public function setLink (HTTPRedirector $link);

	/**
	 * オーサーを設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param MailAddress $email メールアドレス
	 */
	public function setAuthor ($name, MailAddress $email = null);

	/**
	 * 日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate ();

	/**
	 * 日付を設定
	 *
	 * @access public
	 * @param Date $date 日付
	 */
	public function setDate (Date $date);

	/**
	 * エントリーを生成して返す
	 *
	 * @access public
	 * @return FeedEntry エントリー
	 */
	public function createEntry ();

	/**
	 * エントリーのタイトルを配列で返す
	 *
	 * @access public
	 * @return Tuple
	 */
	public function getEntryTitles ();
}
