<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime
 */

namespace Carrot3;

/**
 * 基底MIME文書
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MIMEDocument extends ParameterHolder implements Renderer {
	use BasicObject;
	protected $headers;
	protected $contents;
	protected $body;
	protected $renderer;
	protected $date;
	protected $filename;
	protected $boundary;
	protected $parts;
	const LINE_SEPARATOR = "\r\n";

	/**
	 * ヘッダを返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return MIMEHeader ヘッダ
	 */
	public function getHeader ($name) {
		$header = MIMEHeader::create($name);
		$name = StringUtils::toLower($header->getName());
		return $this->getHeaders()[$name];
	}

	/**
	 * ヘッダを設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param string $value 値
	 */
	public function setHeader ($name, $value) {
		$header = MIMEHeader::create($name);
		if ($header->isMultiple() && $this->getHeader($name)) {
			$header = $this->getHeader($name);
			$header->setContents($value);
		} else {
			$header->setPart($this);
			$header->setContents($value);
			$this->getHeaders()->setParameter(StringUtils::toLower($header->getName()), $header);
		}
		$this->contents = null;
	}

	/**
	 * ヘッダに追記
	 *
	 * @access public
	 * @param string $name 名前
	 * @param string $value 値
	 */
	public function appendHeader ($name, $value) {
		if ($header = $this->getHeader($name)) {
			$header->appendContents($value);
			$this->contents = null;
		} else {
			$this->setHeader($name, $value);
		}
	}

	/**
	 * ヘッダを削除
	 *
	 * @access public
	 * @param string $name 名前
	 */
	public function removeHeader ($name) {
		if ($header = $this->getHeader($name)) {
			$this->getHeaders()->removeParameter(StringUtils::toLower($header->getName()));
			$this->contents = null;
		}
	}

	/**
	 * ヘッダ一式を返す
	 *
	 * @access public
	 * @return string[] ヘッダ一式
	 */
	public function getHeaders () {
		if (!$this->headers) {
			$this->headers = Tuple::create();
		}
		return $this->headers;
	}

	/**
	 * メッセージIDを返す
	 *
	 * @access public
	 * @return string メッセージID
	 */
	public function getMessageID () {
		if ($header = $this->getHeader('Message-Id')) {
			return $header->getEntity();
		}
	}

	/**
	 * 送信日付を返す
	 *
	 * @access public
	 * @return Date 送信日付
	 */
	public function getDate () {
		if (!$this->date && ($header = $this->getHeader('date'))) {
			$this->date = $header->getEntity();
		}
		return $this->date;
	}

	/**
	 * Content-Transfer-Encodingを返す
	 *
	 * @access public
	 * @return string Content-Transfer-Encoding
	 */
	public function getContentTransferEncoding () {
		if ($header = $this->getHeader('Content-Transfer-Encoding')) {
			return $header->getContents();
		}
	}

	/**
	 * レンダラーを返す
	 *
	 * @access public
	 * @return Renderer レンダラー
	 */
	public function getRenderer () {
		if (!$this->renderer) {
			$this->setRenderer(new RawRenderer);
		}
		return $this->renderer;
	}

	/**
	 * レンダラーを設定
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @param int $flags フラグのビット列
	 *   MIMEUtils::WITHOUT_HEADER ヘッダを修正しない
	 *   MIMEUtils::WITH_HEADER ヘッダも修正
	 */
	public function setRenderer (Renderer $renderer, int $flags = MIMEUtils::WITH_HEADER) {
		$this->renderer = $renderer;
		if ($flags & MIMEUtils::WITH_HEADER) {
			$this->setHeader('Content-Type', $renderer);
			$this->setHeader('Content-Transfer-Encoding', $renderer);
		}
	}

	/**
	 * ファイル名を返す
	 *
	 * @access public
	 * @return string ファイル名
	 */
	public function getFileName () {
		return $this->filename;
	}

	/**
	 * ファイル名を設定
	 *
	 * @access public
	 * @param string $filename ファイル名
	 * @param string $mode モード
	 */
	public function setFileName ($filename, $mode = MIMEUtils::ATTACHMENT) {
		if (StringUtils::isBlank($filename)) {
			$this->removeHeader('Content-Disposition');
		} else {
			$this->filename = $filename;
			$value = sprintf('%s; filename="%s"', $mode, $filename);
			$this->setHeader('Content-Disposition', $value);
		}
	}

	/**
	 * 全てのパートを返す
	 *
	 * @access public
	 * @return Tuple 全てのパート
	 */
	public function getParts () {
		if (!$this->parts) {
			$this->parts = Tuple::create();
		}
		return $this->parts;
	}

	/**
	 * マルチパートか？
	 *
	 * @access public
	 * @return bool マルチパートならばTrue
	 */
	public function isMultiPart () {
		if (!!$this->getParts()->count()) {
			return true;
		} else {
			if ($header = $this->getHeader('Content-Type')) {
				if (mb_eregi('^multipart/', $header->getContents())) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents () {
		if (!$this->contents) {
			foreach ($this->getHeaders() as $header) {
				$this->contents .= $header->format();
			}
			$this->contents .= self::LINE_SEPARATOR;
			$this->contents .= $this->getBody();
		}
		return $this->contents;
	}

	/**
	 * 出力内容を設定
	 *
	 * @param string $contents 出力内容
	 * @access public
	 */
	public function setContents ($contents) {
		foreach ([self::LINE_SEPARATOR, "\n"] as $separator) {
			$delimiter = $separator . $separator;
			try {
				$parts = StringUtils::explode($delimiter, $contents);
				if (1 < $parts->count()) {
					$this->parseHeaders($parts->shift());
					$this->parseBody($parts->join($delimiter));
					return;
				}
			} catch (\Exception $e) {
			}
		}
		throw new MIMEException('MIME文書がパースできません。');
	}

	/**
	 * 出力内容をクリア
	 *
	 * @access public
	 */
	public function clearContents () {
		$this->contents = null;
		$this->body = null;
	}

	/**
	 * ヘッダ部をパース
	 *
	 * @access protected
	 * @param string $headers ヘッダ部
	 */
	protected function parseHeaders ($headers) {
		$this->getHeaders()->clear();
		$headers = StringUtils::convertLineSeparator($headers);
		foreach (StringUtils::explode("\n", $headers) as $line) {
			if (mb_ereg('^([-[:alnum:]]+): *(.*)$', $line, $matches)) {
				$key = $matches[1];
				$this->setHeader($key, $matches[2]);
			} else if (mb_ereg('^[[:blank:]]+(.*)$', $line, $matches)) {
				$this->appendHeader($key, $matches[1]);
			}
		}
	}

	/**
	 * 本文をパース
	 *
	 * @access protected
	 * @param string $body 本文
	 */
	protected function parseBody ($body) {
		if ($this->isMultiPart()) {
			$separator = '--' . $this->getBoundary();
			$parts = StringUtils::explode($separator, $body);
			$parts->pop();
			$parts->shift();
			foreach ($parts as $source) {
				$part = new MIMEDocument;
				$part->setContents($source);
				$this->getParts()->push($part);
			}
		} else {
			if ($header = $this->getHeader('Content-Type')) {
				if ($header['main_type'] == 'text') {
					$renderer = new PlainTextRenderer;
					$renderer->setLineSeparator(self::LINE_SEPARATOR);
					$body = StringUtils::convertLineSeparator($body);
					if ($encoding = $header['charset']) {
						$renderer->setEncoding($encoding);
						$body = StringUtils::convertEncoding($body, 'utf-8', $encoding);
					} else {
						$body = StringUtils::convertEncoding($body);
					}
					$this->setRenderer($renderer, MIMEUtils::WITHOUT_HEADER);
				}
			}
			$this->getRenderer()->setContents($body);
		}
	}

	/**
	 * 本文を返す
	 *
	 * マルチパートの場合、素（multipart/mixed）の本文を返す。
	 *
	 * @access public
	 * @return string 本文
	 */
	public function getBody () {
		if (!$this->body) {
			if ($renderer = $this->getRenderer()) {
				$body = $renderer->getContents();
				if ($this->getContentTransferEncoding() == 'base64') {
					$body = MIMEUtils::encodeBase64($body, MIMEUtils::WITH_SPLIT);
				}
				$this->body .= $body;
			}
			if ($this->isMultiPart()) {
				foreach ($this->getParts() as $part) {
					$this->body .= '--' . $this->getBoundary() . self::LINE_SEPARATOR;
					$this->body .= $part->getContents();
				}
				$this->body .= '--' . $this->getBoundary() . '--';
			}
		}
		return $this->body;
	}

	/**
	 * 本文を設定
	 *
	 * マルチパートの場合でも、メインパートの本文を設定する。
	 *
	 * @access public
	 * @param string $body 本文
	 */
	public function setBody ($body) {
		if (!method_exists($this->getRenderer(), 'setContents')) {
			throw new MIMEException(get_glass($renderer) . 'の本文を上書きできません。');
		}
		$this->getRenderer()->setContents($body);
	}

	/**
	 * 本文や添付ファイルの実体を返す
	 *
	 * マルチパートメールの場合は配列
	 *
	 * @access public
	 * @return mixed
	 */
	public function getEntities () {
		if ($this->isMultiPart()) {
			$parts = Tuple::create();
			foreach ($this->getParts() as $part) {
				$name = null;
				if ($header = $part->getHeader('Content-Disposition')) {
					$name = $header['filename'];
				}
				$parts[$name] = $part->getEntities();
			}
			return $parts;
		} else {
			$entity = $this->getRenderer()->getContents();
			if ($header = $this->getHeader('Content-Type')) {
				switch ($this->getContentTransferEncoding()) {
					case 'base64':
						$entity = MIMEUtils::decodeBase64($entity);
						break;
					case 'quoted-printable':
						$entity = MIMEUtils::decodeQuotedPrintable($entity);
						break;
				}
				if ($header['main_type'] == 'text') {
					$entity = StringUtils::convertEncoding($entity, 'utf-8', $header['charset']);
				}
			}
			return $entity;
		}
	}

	/**
	 * 添付ファイルを追加
	 *
	 * @access public
	 * @param Renderer $renderer レンダラー
	 * @param string $name ファイル名
	 * @return MIMEDocument 追加されたパート
	 */
	public function addAttachment (Renderer $renderer, $name = null) {
		$part = new MIMEDocument;
		$part->setRenderer($renderer);
		if (!StringUtils::isBlank($name)) {
			$part->setFileName($name, MIMEUtils::ATTACHMENT);
		}

		$this->getParts()->push($part);
		$this->body = null;
		$this->contents = null;

		if ($this->isMultiPart()) {
			$this->setHeader('Content-Type', 'multipart/mixed; boundary=' . $this->getBoundary());
			$this->setHeader('Content-Transfer-Encoding', null);
		} else {
			$this->setHeader('Content-Type', $renderer);
			$this->setHeader('Content-Transfer-Encoding', $renderer);
		}

		return $part;
	}

	/**
	 * バウンダリを返す
	 *
	 * @access public
	 * @return string バウンダリ
	 */
	public function getBoundary () {
		if (!$this->boundary) {
			$this->boundary = Utils::getUniqueID();
		}
		return $this->boundary;
	}

	/**
	 * バウンダリを設定
	 *
	 * @access public
	 * @param string $boundary バウンダリ
	 */
	public function setBoundary ($boundary) {
		$this->boundary = $boundary;
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize () {
		return strlen($this->getContents());
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType('mime');
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate () {
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return null;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('MIME文書 "%s"', $this->getMessageID());
	}
}
