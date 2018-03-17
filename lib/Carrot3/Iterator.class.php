<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * 基底イテレータ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Iterator implements \Iterator, \Countable {
	protected $keys = [];
	protected $values = [];
	protected $cursor;

	/**
	 * @access public
	 * @param $array 対象配列
	 */
	public function __construct ($array) {
		if ($array instanceof ParameterHolder) {
			$this->values = $array->getParameters();
		} else if (is_array($array)) {
			$this->values = $array;
		} else {
			throw new \InvalidArgumentException('引数は配列ではありません。');
		}
		$this->keys = array_keys($this->values);
	}

	/**
	 * カーソルを巻き戻す
	 *
	 * @access public
	 * @return mixed 最初のエントリー
	 */
	public function rewind () {
		$this->cursor = 0;
		return $this->current();
	}

	/**
	 * 最初のエントリーを返す
	 *
	 * forwardのエイリアス
	 *
	 * @access public
	 * @return mixed 最初のエントリー
	 * @final
	 */
	final public function getFirst () {
		return $this->rewind();
	}

	/**
	 * カーソルを終端に進める
	 *
	 * @access public
	 * @return mixed 最後のエントリー
	 */
	public function forward () {
		$this->cursor = count($this->values) - 1;
		return $this->current();
	}

	/**
	 * 最後のエントリーを返す
	 *
	 * rewindのエイリアス
	 *
	 * @access public
	 * @return mixed 最後のエントリー
	 * @final
	 */
	final public function getLast () {
		return $this->forward();
	}

	/**
	 * カーソルは最初か？
	 *
	 * @access public
	 * @return bool 最初ならTrue
	 */
	public function isFirst () {
		return ($this->cursor == 0);
	}

	/**
	 * カーソルは最後か？
	 *
	 * @access public
	 * @return bool 最後ならTrue
	 */
	public function isLast () {
		return ($this->cursor == (count($this->values) - 1));
	}

	/**
	 * 現在のエントリーを返す
	 *
	 * @access public
	 * @return mixed エントリー
	 */
	public function current () {
		if ($this->valid()) {
			return $this->values[$this->key()];
		}
	}

	/**
	 * 次のエントリーを返す
	 *
	 * @access public
	 * @return mixed エントリー
	 */
	public function next () {
		$this->cursor ++;
		return $this->current();
	}

	/**
	 * 前のエントリーを返す
	 *
	 * @access public
	 * @return mixed エントリー
	 */
	public function prev () {
		$this->cursor --;
		return $this->current();
	}

	/**
	 * 現在のカーソル位置を返す
	 *
	 * @access public
	 * @return integer カーソル位置
	 */
	public function key () {
		if ($this->valid()) {
			return $this->keys[$this->cursor];
		}
	}

	/**
	 * 現在のカーソル位置に正しいエントリーが存在するか？
	 *
	 * @access public
	 * @return bool 正しいエントリーが存在するならTrue
	 */
	public function valid () {
		return isset($this->keys[$this->cursor]);
	}

	/**
	 * @access public
	 * @return integer 要素数
	 */
	public function count () {
		return count($this->keys);
	}
}
