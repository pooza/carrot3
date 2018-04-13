<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.json
 */

namespace Carrot3;

/**
 * JSONレンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class JSONRenderer implements Renderer, MessageContainer {
	protected $serializer;
	protected $contents;
	protected $result;

	/**
	 * シリアライザーを返す
	 *
	 * @access protected
	 * @return JSONSerializer
	 */
	protected function getSerializer () {
		if (!$this->serializer) {
			$this->serializer = new JSONSerializer;
		}
		return $this->serializer;
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents ():string {
		return $this->contents;
	}

	/**
	 * メッセージ文字列を返す
	 *
	 * @access public
	 * @return string メッセージ文字列
	 */
	public function getMessage () {
		return $this->getContents();
	}

	/**
	 * 出力内容を設定
	 *
	 * @param string $contents 出力内容
	 * @access public
	 */
	public function setContents ($contents) {
		if (is_iterable($contents)) {
			$contents = Tuple::create($contents);
			$this->result = $contents->decode();
			$contents = $this->getSerializer()->encode(
				$this->result,
				JSON_PRETTY_PRINT
			);
		}
		$this->contents = $contents;
	}

	/**
	 * パース結果を返す
	 *
	 * @access public
	 */
	public function getResult () {
		if (!$this->result) {
			$this->result = $this->getSerializer()->decode($this->getContents());
		}
		return $this->result;
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
		return MIMEType::getType('json');
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
}
