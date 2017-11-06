<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail.pop3
 */

namespace Carrot3;

/**
 * 受信メール
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class POP3Mail extends MIMEDocument {
	private $id;
	private $size;
	private $server;
	private $executed;

	/**
	 * @access public
	 * @param POP3 $server サーバ
	 * @param string $line レスポンス行
	 */
	public function __construct (POP3 $server, $line) {
		$fields = StringUtils::explode(' ', $line);
		$this->id = $fields[0];
		$this->size = $fields[1];
		$this->server = $server;
		$this->executed = Tuple::create();
	}

	/**
	 * IDを返す
	 *
	 * @access public
	 * @return integer ID
	 */
	public function getID () {
		return $this->id;
	}

	/**
	 * ヘッダを返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return MIMEHeader ヘッダ
	 */
	public function getHeader ($name) {
		if (!$this->getHeaders()->count()) {
			$this->fetchHeaders();
		}
		return parent::getHeader($name);
	}

	/**
	 * 本文を取得
	 *
	 * @access public
	 */
	public function fetch () {
		$this->server->execute('RETR ' . $this->getID());
		$body = Tuple::create($this->server->getLines());
		$this->setContents($body->join("\n"));
		$this->executed['RETR'] = true;
	}

	/**
	 * ヘッダだけを取得
	 *
	 * @access public
	 */
	public function fetchHeaders () {
		$this->server->execute('TOP ' . $this->getID() . ' 0');
		$this->parseHeaders($this->server->getLines()->join("\n"));
		$this->executed['TOP'] = true;
	}

	/**
	 * 本文を返す
	 *
	 * 添付メールの場合でも、素の本文を返す。
	 *
	 * @access public
	 * @return string 本文
	 */
	public function getBody () {
		if (!$this->executed['RETR']) {
			$this->fetch();
		}
		return parent::getBody();
	}

	/**
	 * メールのサイズをPOPセッションから取得して返す
	 *
	 * @access public
	 * @return integer サイズ
	 */
	public function getMailSize () {
		return $this->size;
	}

	/**
	 * サーバから削除
	 *
	 * @access public
	 */
	public function delete () {
		if (!$this->executed['DELE']) {
			$this->server->execute('DELE ' . $this->getID());
			$message = new StringFormat('%sを%sから削除しました。');
			$message[] = $this;
			$message[] = $this->server;
			LogManager::getInstance()->put($message, $this);
			$this->executed['DELE'] = true;
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('POP3メール "%s"', $this->getMessageID());
	}
}
