<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail.pop3
 */

namespace Carrot3;

/**
 * POP3プロトコル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class POP3 extends Socket {
	private $mails;

	/**
	 * ストリームを開く
	 *
	 * @access public
	 */
	public function open () {
		parent::open();
		if (!$this->isSuccess()) {
			$message = new StringFormat('%sに接続できません。 (%s)');
			$message[] = $this;
			$message[] = $this->getPrevLine();
			$this->client = null;
			throw new MailException($message);
		}
	}

	/**
	 * ストリームを閉じる
	 *
	 * @access public
	 */
	public function close () {
		$this->execute('QUIT');
		parent::close();
	}

	/**
	 * ストリームの終端まで読んで返す
	 *
	 * 最終行のドットは削除
	 *
	 * @access public
	 * @return Tuple 読み込んだ内容
	 */
	public function getLines ():Tuple {
		$lines = Tuple::create();
		do {
			$line = $this->getLine();
			$lines[] = $line;
		} while ($line != '.');
		$lines->pop();
		return $lines;
	}

	/**
	 * 認証
	 *
	 * @access public
	 * @param string $user ユーザー名
	 * @param string $password パスワード
	 * @return bool 認証の成否
	 */
	public function auth ($user, $password) {
		return ($this->execute('USER ' . $user) && $this->execute('PASS ' . $password));
	}

	/**
	 * サーバに残っているメールを全て返す
	 *
	 * @access public
	 * @return Tuple 全てのメール
	 */
	public function getMails () {
		if (!$this->mails) {
			$this->mails = Tuple::create();
			if (!$this->isOpened()) {
				$this->open();
			}

			$this->execute('LIST');
			foreach ($this->getLines() as $line) {
				$mail = new POP3Mail($this, $line);
				$this->mails[$mail->getID()] = $mail;
			}
		}
		return $this->mails;
	}

	/**
	 * サーバに残っているメールを返す
	 *
	 * @access public
	 * @param int $id メールの番号
	 * @return POP3Mail メール
	 */
	public function getMail (int $id) {
		return $this->getMails()[$id];
	}

	/**
	 * コマンドを実行し、結果を返す。
	 *
	 * @access public
	 * @param string $command コマンド
	 * @return bool 成功ならばTrue
	 */
	public function execute ($command) {
		$this->putLine($command);
		if (!$this->isSuccess()) {
			$message = new StringFormat('%s (%s)');
			$message[] = $this->getPrevLine();
			$message[] = $command;
			$this->client = null;
			throw new MailException($message);
		}
		return true;
	}

	/**
	 * 直前のコマンドは実行に成功したか？
	 *
	 * @access public
	 * @return bool 成功ならばTrue
	 */
	public function isSuccess ():bool {
		return mb_ereg('^\\+OK', $this->getLine());
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('POP3ソケット "%s"', $this->getName());
	}

	/**
	 * 規定のポートを返す
	 *
	 * @access public
	 * @return int port
	 */
	public function getDefaultPort () {
		return NetworkService::getPort('pop3');
	}
}
