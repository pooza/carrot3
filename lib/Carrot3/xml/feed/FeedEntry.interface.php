<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed
 */

namespace Carrot3;

/**
 * フィードエントリー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
interface FeedEntry {

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
	 * 本文を設定
	 *
	 * @access public
	 * @param string $content 内容
	 */
	public function setBody ($body = null);

	/**
	 * 親文書を設定
	 *
	 * @access public
	 * @param FeedDocument $document 親文書
	 */
	public function setDocument (FeedDocument $document);
}

