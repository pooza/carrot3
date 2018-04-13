<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.parser
 */

namespace Carrot3;
require_once BS_LIB_DIR . '/Spyc.php';

/**
 * YAML設定パーサー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class YAMLConfigParser extends \Spyc implements ConfigParser {
	private $contents;
	private $result;

	/**
	 * 変換前の設定内容を返す
	 *
	 * @access public
	 * @return string 設定内容
	 */
	public function getContents ():string {
		if (!$this->contents && $this->result) {
			$this->contents = $this->dump($this->result);
		}
		return $this->contents;
	}

	/**
	 * 変換前の設定内容を設定
	 *
	 * @access public
	 * @param string $contents 設定内容
	 */
	public function setContents ($contents) {
		$this->contents = StringUtils::convertKana($contents, 'KVa');
		$this->result = null;
	}

	/**
	 * 変換後の設定内容を返す
	 *
	 * @access public
	 * @return Tuple 設定内容
	 */
	public function getResult () {
		if (!$this->result && !StringUtils::isBlank($this->contents)) {
			$this->result = Tuple::create(parent::YAMLLoad($this->contents));
		}
		return $this->result;
	}

	/**
	 * 結果配列を設定
	 *
	 * @access public
	 * @param mixed $result 結果配列
	 */
	public function setResult ($result) {
		if ($result instanceof ParameterHolder) {
			$this->result = $result->getParameters();
			$this->contents = null;
		} else {
			$this->result = $result;
			$this->contents = null;
		}
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
		return MIMEType::getType('yaml');
	}

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding ():string {
		return 'utf-8';
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
		return !!$this->getResult();
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return '要素が含まれていません。';
	}
}
