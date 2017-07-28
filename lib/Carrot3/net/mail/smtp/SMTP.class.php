<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mail.smtp
 */

namespace Carrot3;

/**
 * SMTPプロトコル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class SMTP extends Socket {
	private $mail;
	private $keywords;
	const TEST = 1;

	/**
	 * @access public
	 * @param mixed $host ホスト
	 * @param integer $port ポート
	 * @param string $protocol プロトコル
	 *   NetworkService::TCP
	 *   NetworkService::UDP
	 */
	public function __construct ($host = null, $port = null, $protocol = NetworkService::TCP) {
		if (StringUtils::isBlank($host)) {
			$host = new Host(BS_SMTP_HOST);
		}
		parent::__construct($host, $port, $protocol);
		$this->setMail(new Mail);
		$this->keywords = Tuple::create();
	}

	/**
	 * ストリームを開く
	 *
	 * @access public
	 */
	public function open () {
		parent::open();
		stream_set_timeout($this->client, 0, BS_SMTP_TIMEOUT);
		$command = 'EHLO ' . $this->controller->getHost()->getName();
		if (!in_array($this->execute($command), [220, 250])) {
			$message = new StringFormat('%sに接続できません。 (%s)');
			$message[] = $this;
			$message[] = $this->getPrevLine();
			throw new MailException($message);
		}
		while (!StringUtils::isBlank($line = $this->getLine())) {
			$this->keywords[] = $line;
		}
	}

	/**
	 * ストリームを閉じる
	 *
	 * @access public
	 */
	public function close () {
		if ($this->execute('QUIT') != 221) {
			$message = new StringFormat('%sから切断できません。(%s)');
			$message[] = $this;
			$message[] = $this->getPrevLine();
			throw new MailException($message);
		}
		parent::close();
	}

	/**
	 * メールを返す
	 *
	 * @access public
	 * @return Mail メール
	 */
	public function getMail () {
		return $this->mail;
	}

	/**
	 * メールを設定
	 *
	 * @access public
	 * @param Mail $mail メール
	 */
	public function setMail (Mail $mail) {
		$this->mail = $mail;
	}

	/**
	 * 送信
	 *
	 * @access public
	 * @param integer $flags フラグのビット列
	 *   self::TEST テスト送信
	 * @return string 送信完了時は最終のレスポンス
	 */
	public function send ($flags = null) {
		try {
			$this->getMail()->clearMessageID();
			$this->execute('MAIL FROM:' . $this->getFrom()->getContents());
			foreach ($this->getRecipients($flags) as $email) {
				$this->execute('RCPT TO:' . $email->getContents());
			}
			$this->execute('DATA');
			$this->putLine($this->getMail()->getContents());
			if ($this->execute('.') != 250) {
				throw new MailException($this->getPrevLine());
			}
		} catch (MailException $e) {
			throw new MailException($this->getMail() . 'を送信できません。');
		}
		return $this->getPrevLine();
	}

	/**
	 * 送信者を返す
	 *
	 * @access protected
	 * @return MailAddress 送信者
	 */
	protected function getFrom () {
		return $this->getMail()->getHeader('From')->getEntity();
	}

	/**
	 * 受信者を返す
	 *
	 * @access protected
	 * @param integer $flags フラグのビット列
	 *   self::TEST テスト送信
	 * @return Tuple 受信者の配列
	 */
	protected function getRecipients ($flags = null) {
		if (BS_DEBUG || ($flags & self::TEST)) {
			$recipients = Tuple::create();
			$recipients[] = AdministratorRole::getInstance()->getMailAddress();
			return $recipients;
		} else {
			return clone $this->getMail()->getRecipients();
		}
	}

	/**
	 * キーワードを返す
	 *
	 * @access public
	 * @return Tuple キーワード一式
	 */
	public function getKeywords () {
		if (!$this->keywords) {
			$this->keywords = Tuple::create();
		}
		return $this->keywords;
	}

	/**
	 * Subjectを設定
	 *
	 * @access public
	 * @param string $subject Subject
	 */
	public function setSubject ($subject) {
		$this->getMail()->setHeader('Subject', $subject);
	}

	/**
	 * X-Priorityヘッダを設定
	 *
	 * @access public
	 * @param integer $priority X-Priorityヘッダ
	 */
	public function setPriority ($priority) {
		$this->getMail()->setHeader('X-Priority', $priority);
	}

	/**
	 * 送信者を設定
	 *
	 * @access public
	 * @param MailAddress $email 送信者
	 */
	public function setFrom (MailAddress $email) {
		$this->getMail()->setHeader('From', $email);
	}

	/**
	 * 宛先を設定
	 *
	 * @access public
	 * @param MailAddress $email 宛先
	 */
	public function setTo (MailAddress $email) {
		$this->getMail()->setHeader('To', $email);
	}

	/**
	 * BCCを加える
	 *
	 * @access public
	 * @param MailAddress $bcc 宛先
	 */
	public function addBCC (MailAddress $bcc) {
		$this->getMail()->getHeader('BCC')->appendContents($bcc);
	}

	/**
	 * 本文を返す
	 *
	 * @access public
	 * @param string $body 本文
	 */
	public function getBody () {
		return $this->getMail()->getBody();
	}

	/**
	 * 本文を設定
	 *
	 * @access public
	 * @param string $body 本文
	 */
	public function setBody ($body) {
		return $this->getMail()->setBody($body);
	}

	/**
	 * コマンドを実行し、結果を返す。
	 *
	 * @access public
	 * @param string $command コマンド
	 * @return integer 結果コード
	 */
	public function execute ($command) {
		$this->putLine($command);
		if (!mb_ereg('^([[:digit:]]+)', $this->getLine(), $matches)) {
			$message = new StringFormat('不正なレスポンスです。 (%s)');
			$message[] = $this->getPrevLine();
			throw new MailException($message);
		}
		if (400 <= ($result = $matches[1])) {
			$message = new StringFormat('%s (%s)');
			$message[] = $this->getPrevLine();
			$message[] = $command;
			throw new MailException($message);
		}
		return $result;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('SMTPソケット "%s"', $this->getName());
	}

	/**
	 * 規定のポート番号を返す
	 *
	 * @access public
	 * @return integer port
	 */
	public function getDefaultPort () {
		return NetworkService::getPort('smtp');
	}
}

