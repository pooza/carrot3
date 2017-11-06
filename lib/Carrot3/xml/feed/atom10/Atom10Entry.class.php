<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.atom10
 */

namespace Carrot3;

/**
 * Atom1.0エントリー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Atom10Entry extends XMLElement implements FeedEntry {
	protected $document;

	/**
	 * リンクを返す
	 *
	 * @access public
	 * @return HTTPURL リンク
	 */
	public function getLink () {
		if ($element = $this->getElement('link')) {
			return URL::create($element->getBody());
		}
	}

	/**
	 * リンクを設定
	 *
	 * @access public
	 * @param HTTPRedirector $link リンク
	 */
	public function setLink (HTTPRedirector $link) {
		if (!$element = $this->getElement('id')) {
			$element = $this->createElement('id');
		}
		$element->setBody(self::getID($link->getURL()));

		if (!$element = $this->getElement('link')) {
			$element = $this->createElement('link');
		}
		$element->setBody($link->getURL()->getContents());
	}

	/**
	 * タイトルを返す
	 *
	 * @access public
	 * @return string タイトル
	 */
	public function getTitle () {
		if ($element = $this->getElement('title')) {
			return $element->getBody();
		}
	}

	/**
	 * タイトルを設定
	 *
	 * @access public
	 * @param string $title タイトル
	 */
	public function setTitle ($title) {
		if (!$element = $this->getElement('title')) {
			$element = $this->createElement('title');
		}
		$element->setBody($title);
	}

	/**
	 * 日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate () {
		if ($element = $this->getElement('published')) {
			return Date::create($element->getBody());
		}
	}

	/**
	 * 日付を設定
	 *
	 * @access public
	 * @param Date $date 日付
	 */
	public function setDate (Date $date) {
		if (!$element = $this->getElement('published')) {
			$element = $this->createElement('published');
		}
		$element->setBody($date->format(\DateTime::ATOM));
	}

	/**
	 * 本文を設定
	 *
	 * @access public
	 * @param string $content 内容
	 */
	public function setBody ($body = null) {
		if (!$element = $this->getElement('summary')) {
			$element = $this->createElement('summary');
		}
		$element->setBody(StringUtils::sanitize($body));
	}

	/**
	 * 親文書を設定
	 *
	 * @access public
	 * @param FeedDocument $document 親文書
	 */
	public function setDocument (FeedDocument $document) {
		$this->document = $document;
		$this->setName($document->getEntryElementName());
	}

	/**
	 * パーマリンクからIDを生成
	 *
	 * @access public
	 * @param HTTPRedirector $link パーマリンク
	 * @return string ID
	 * @link http://diveintomark.org/archives/2004/05/28/howto-atom-id 参考
	 */
	static public function getID (HTTPRedirector $link) {
		$url = $link->getURL();
		$id = $url->getContents();
		$id = str_replace($url['scheme'] . '://', '', $id);

		if ($auth = $url['user']) {
			if ($pass = $url['pass']) {
				$auth .= ':' . $pass;
			}
			$auth .= '@';
			$id = str_replace($auth, '', $id);
		}

		$id = str_replace('#', '/', $id);

		$host = $url['host']->getName();
		$date = Date::create()->format(',Y-m-d:');
		$id = str_replace($host, $host . $date, $id);

		return 'tag:' . $id;
	}
}
