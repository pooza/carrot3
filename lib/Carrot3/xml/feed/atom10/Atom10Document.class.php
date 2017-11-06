<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage xml.feed.atom10
 */

namespace Carrot3;

/**
 * Atom1.0文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Atom10Document extends XMLDocument implements FeedDocument {
	protected $namespace = 'http://www.w3.org/2005/Atom';

	/**
	 * @access public
	 * @param string $name 要素の名前
	 */
	public function __construct ($name = null) {
		parent::__construct('feed');
		$this->setNamespace($this->namespace);
		$this->setDate(Date::create());
		$generator = $this->createElement('generator', $this->controller->getName());
		$generator->setAttribute('uri', BS_APP_URL);
		$generator->setAttribute('version', BS_APP_VER);
		$author = AuthorRole::getInstance();
		$this->setAuthor($author->getName('ja'), $author->getMailAddress('ja'));
		$this->setAttribute('xml:lang', 'ja');
	}

	/**
	 * エントリー要素の名前を返す
	 *
	 * @access public
	 * @return string
	 */
	public function getEntryElementName () {
		return 'entry';
	}

	/**
	 * エントリー要素要素の格納先を返す
	 *
	 * @access public
	 * @return XMLElement
	 */
	public function getEntryRootElement () {
		return $this;
	}

	/**
	 * 妥当な文書か？
	 *
	 * @access public
	 * @return boolean 妥当な文書ならTrue
	 */
	public function validate () {
		return (parent::validate()
			&& $this->query('/feed/title')
			&& $this->query('/feed/updated')
			&& $this->query('/feed/link')
		);
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType('atom');
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
	 * ディスクリプションを設定
	 *
	 * @access public
	 * @param string $description ディスクリプション
	 */
	public function setDescription ($description) {
		if (!$element = $this->getElement('subtitle')) {
			$element = $this->createElement('subtitle');
		}
		$element->setBody($description);
	}

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
		$element->setBody(Atom10Entry::getID($link->getURL()));

		if (!$element = $this->getElement('link')) {
			$element = $this->createElement('link');
		}
		$element->setBody($link->getURL()->getContents());
	}

	/**
	 * オーサーを設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param MailAddress $email メールアドレス
	 */
	public function setAuthor ($name, MailAddress $email = null) {
		if (!$author = $this->getElement('author')) {
			$author = $this->createElement('author');
		}

		if (!$element = $author->getElement('name')) {
			$element = $author->createElement('name');
		}
		$element->setBody($name);

		if ($email) {
			if (!$element = $author->getElement('email')) {
				$element = $author->createElement('email');
			}
			$element->setBody($email->getContents());
		}
	}

	/**
	 * 日付を返す
	 *
	 * @access public
	 * @return Date 日付
	 */
	public function getDate () {
		if ($element = $this->getElement('updated')) {
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
		if (!$element = $this->getElement('updated')) {
			$element = $this->createElement('updated');
		}
		$element->setBody($date->format(\DateTime::ATOM));
	}

	/**
	 * エントリーを生成して返す
	 *
	 * @access public
	 * @return FeedEntry エントリー
	 */
	public function createEntry () {
		return FeedUtils::createEntry($this);
	}

	/**
	 * エントリーのタイトルを配列で返す
	 *
	 * @access public
	 * @return Tuple
	 */
	public function getEntryTitles () {
		return FeedUtils::getEntryTitles($this);
	}
}
