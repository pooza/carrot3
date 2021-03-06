<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer
 */

namespace Carrot3;

/**
 * vCard2.1レンダラー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class VCardRenderer extends ParameterHolder implements Renderer {
	private $contents;

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents ():string {
		if (!$this->contents) {
			$contents = Tuple::create();
			$contents[] = 'BEGIN:VCARD';
			$contents[] = 'VERSION:2.1';
			foreach ($this as $key => $value) {
				$contents[] = $this->getFieldContents($key);
			}
			$contents[] = 'END:VCARD';
			$this->contents = $contents->join("\r\n");
		}
		return $this->contents;
	}

	private function getFieldContents ($key) {
		$entry = Tuple::create($this->params[$key]);
		$body = $entry->pop();
		foreach ($entry as $param) {
			if (mb_eregi('^charset=(.*)$', $param, $matches)) {
				$body = StringUtils::convertEncoding($body, $matches[1]);
				break;
			}
		}
		$entry->unshift($key);
		return $entry->join(';') . ':' . $body;
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
		return MIMEType::getType('vcf');
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
	public function getError ():?string {
		return null;
	}
}
