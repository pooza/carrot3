<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.rss09
 */

namespace Carrot3;

/**
 * RSS0.9xエントリー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class RSS09Entry extends XMLElement implements FeedEntry {
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
		if ($element = $this->getElement('pubDate')) {
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
		if (!$element = $this->getElement('pubDate')) {
			$element = $this->createElement('pubDate');
		}
		$element->setBody($date->format(\DateTime::RSS));
	}

	/**
	 * 本文を設定
	 *
	 * @access public
	 * @param string $content 本文
	 */
	public function setBody ($body = null) {
		if (!$element = $this->getElement('description')) {
			$element = $this->createElement('description');
			$element->setRawMode(true);
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
}
