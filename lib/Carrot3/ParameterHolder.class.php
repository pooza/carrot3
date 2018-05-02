<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * パラメータホルダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class ParameterHolder implements \IteratorAggregate, \ArrayAccess, \Countable, Assignable, MessageContainer {
	protected $params = [];

	/**
	 * パラメータを返す
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return mixed パラメータ
	 */
	public function getParameter (?string $name) {
		if ($this->hasParameter($name)) {
			return $this->params[$name];
		}
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @param mixed $value 値
	 */
	public function setParameter (?string $name, $value) {
		$this->params[$name] = $value;
	}

	/**
	 * 全てのパラメータを返す
	 *
	 * @access public
	 * @return array 全てのパラメータ
	 */
	public function getParameters () {
		return $this->params;
	}

	/**
	 * パラメータをまとめて設定
	 *
	 * @access public
	 * @param mixed $params パラメータの配列
	 */
	public function setParameters ($params) {
		if ($params instanceof ParameterHolder) {
			$params = $params->getParameters();
		} else if (Numeric::isZero($params)) {
			$params = [0];
		} else if (!$params) {
			return;
		}
		foreach ((array)$params as $name => $value) {
			$this->setParameter($name, $value);
		}
	}

	/**
	 * パラメータが存在するか？
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return bool 存在すればTrue
	 */
	public function hasParameter (?string $name):bool {
		return array_key_exists($name, $this->params);
	}

	/**
	 * パラメータを削除
	 *
	 * @access public
	 * @param string $name パラメータ名
	 */
	public function removeParameter (string $name) {
		if ($this->hasParameter($name)) {
			unset($this->params[$name]);
		}
	}

	/**
	 * 全てのパラメータを削除
	 *
	 * clearParametersのエイリアス
	 *
	 * @access public
	 * @final
	 */
	final public function clear () {
		$this->clearParameters();
	}

	/**
	 * 全てのパラメータを削除
	 *
	 * @access public
	 */
	public function clearParameters () {
		foreach ($this as $name => $value) {
			$this->removeParameter($name);
		}
	}

	/**
	 * @access public
	 * @return Iterator イテレータ
	 */
	public function getIterator () {
		return new Iterator($this->getParameters());
	}

	/**
	 * @access public
	 * @return int 要素数
	 */
	public function count () {
		return count($this->getParameters());
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return $this->hasParameter($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->getParameter($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed 要素
	 */
	public function offsetSet ($key, $value) {
		$this->setParameter($key, $value);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		$this->removeParameter($key);
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getParameters();
	}

	/**
	 * メッセージ文字列を返す
	 *
	 * @access public
	 * @return string メッセージ文字列
	 */
	public function getMessage ():string {
		return (new JSONSerializer)->encode($this->getParameters());
	}

	/**
	 * クラス名を返す
	 *
	 * @access public
	 * @return string クラス名
	 */
	public function getName ():string {
		return Utils::getClass($this);
	}
}
