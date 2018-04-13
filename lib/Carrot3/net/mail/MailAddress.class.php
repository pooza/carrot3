<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail
 */

namespace Carrot3;

/**
 * メールアドレス
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MailAddress implements Assignable {
	use BasicObject;
	private $contents;
	private $name;
	private $account;
	private $domain;
	private $url;
	private $mx = [];

	/**
	 * @access private
	 * @param string $contents メールアドレス
	 * @param string $name 名前
	 */
	private function __construct ($contents, string $name = null) {
		if (StringUtils::isBlank($name) && mb_ereg('^(.+) *<(.+)>$', $contents, $matches)) {
			$name = $matches[1];
			$contents = $matches[2];
		}
		if (filter_var($contents, FILTER_VALIDATE_EMAIL)) {
			$this->contents = $contents;
			$this->name = $name;
			mb_ereg('^(.+?)@(.+?)$', $contents, $matches);
			$this->account = $matches[1];
			$this->domain = $matches[2];
		}
	}

	/**
	 * インスタンスを生成して返す
	 *
	 * @access public
	 * @return MailAddress インスタンス
	 * @static
	 */
	static public function create ($contents, string $name = null) {
		$email = new self($contents, $name);
		if (!StringUtils::isBlank($email->getContents())) {
			return $email;
		}
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string メールアドレス
	 */
	public function getContents ():string {
		return $this->contents;
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string 名前
	 */
	public function getName ():string {
		return $this->name;
	}

	/**
	 * ドメイン名を返す
	 *
	 * @access public
	 * @return string ドメイン名
	 */
	public function getDomainName ():string {
		return $this->domain;
	}

	/**
	 * URLを返す
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL ():URL {
		if (!$this->url) {
			$this->url = URL::create('mailto:' . $this->getContents());
		}
		return $this->url;
	}

	/**
	 * メールアドレスを書式化
	 *
	 * @access public
	 * @return string 書式化されたメールアドレス
	 */
	public function format () {
		if (StringUtils::isBlank($this->getName())) {
			return $this->getContents();
		} else {
			return $this->getName() . ' <' . $this->getContents() . '>';
		}
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getContents();
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('メールアドレス "%s"', $this->getContents());
	}
}
