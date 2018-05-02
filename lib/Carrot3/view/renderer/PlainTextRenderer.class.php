<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer
 */

namespace Carrot3;

/**
 * プレーンテキストレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class PlainTextRenderer implements TextRenderer, MessageContainer, \IteratorAggregate {
	private $encoding = 'UTF-8';
	private $lineSeparator = "\n";
	private $convertKanaFlag = 'KV';
	private $option = 0;
	private $width = null;
	private $contents;
	const TAIL_LF = 1;
	const FLOWED = 2;

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents ():string {
		$contents = $this->contents;

		if ($this->convertKanaFlag) {
			$contents = StringUtils::convertKana($contents, $this->convertKanaFlag);
		}
		if ($this->width) {
			$contents = StringUtils::split($contents, $this->width, $this->getOption(self::FLOWED));
		}
		if ($this->getOption(self::TAIL_LF)) {
			$contents .= "\n\n"; //AppleMail対応
		}
		$contents = StringUtils::convertLineSeparator($contents, $this->lineSeparator);
		$contents = StringUtils::convertEncoding($contents, $this->getEncoding());
		return $contents;
	}

	/**
	 * メッセージ文字列を返す
	 *
	 * @access public
	 * @return string メッセージ文字列
	 */
	public function getMessage ():string {
		return $this->getContents();
	}

	/**
	 * 出力内容を設定
	 *
	 * @param string $contents 出力内容
	 * @access public
	 */
	public function setContents ($contents) {
		$this->contents = $contents;
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return int サイズ
	 */
	public function getSize ():int {
		return strlen($this->getContents());
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType ():string {
		return MIMEType::getType('txt');
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
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
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding ():string {
		return $this->encoding;
	}

	/**
	 * エンコードを設定
	 *
	 * @access public
	 * @param string $encoding PHPのエンコード名
	 */
	public function setEncoding (string $encoding) {
		if (StringUtils::isBlank(mb_preferred_mime_name($encoding))) {
			throw new ViewException('利用できないエンコード名です。');
		}
		$this->encoding = $encoding;
	}

	/**
	 * 改行コードを設定
	 *
	 * @access public
	 * @param string $separator 改行コード
	 */
	public function setLineSeparator ($separator) {
		$this->lineSeparator = $separator;
	}

	/**
	 * カナ変換フラグを設定
	 *
	 * @access public
	 * @param string $flag フラグ
	 */
	public function setConvertKanaFlag ($flag) {
		$this->convertKanaFlag = $flag;
	}

	/**
	 * オプションが設定されているか？
	 *
	 * @access public
	 * @param int $option オプション
	 */
	public function getOption (int $option) {
		return ($this->option & $option);
	}

	/**
	 * オプションを設定
	 *
	 * @access public
	 * @param int $option オプションの和
	 *    self::TAIL_LF 末尾に改行を追加
	 */
	public function setOptions (int $option) {
		$this->option |= $option;
	}

	/**
	 * オプションをクリア
	 *
	 * @access public
	 */
	public function clearOptions () {
		$this->option = 0;
	}

	/**
	 * 行幅を設定
	 *
	 * @access public
	 * @param int $width 行幅
	 */
	public function setWidth (int $width) {
		$this->width = $width;
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return new Iterator(StringUtils::explode($this->lineSeparator, $this->contents));
	}
}
