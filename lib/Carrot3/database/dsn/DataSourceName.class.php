<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.dsn
 */

namespace Carrot3;

/**
 * データソース名
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class DataSourceName extends ParameterHolder {
	protected $constants;

	/**
	 * @access public
	 * @param string $contents DSN値
	 * @param string $name DSN名
	 */
	public function __construct (string $contents, string $name = 'default') {
		$this['connection_name'] = $name;
		$this['dbms'] = $this->getDBMS();
		$this['dsn'] = $contents;
		$this['uid'] = $this->getConstant('uid');
		$this['password'] = $this->getConstant('password');
		$this['loggable'] = !!$this->getConstant('loggable');
	}

	/**
	 * DSN名を返す
	 *
	 * @access public
	 * @return string DSN名
	 */
	public function getName () {
		return $this['connection_name'];
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents () {
		return $this['dsn'];
	}

	/**
	 * DBMS名を返す
	 *
	 * @access public
	 * @return string DBMS名
	 * @abstract
	 */
	abstract public function getDBMS ();

	/**
	 * データベースに接続して返す
	 *
	 * @access public
	 * @return Database データベース
	 * @abstract
	 */
	abstract public function connect ();

	/**
	 * 復号したパスワードを返す
	 *
	 * @access public
	 * @return string パスワード
	 */
	public function decryptPassword () {
		return Crypt::getInstance()->decrypt($this['password']);
	}

	/**
	 * 定数を返す
	 *
	 * @access public
	 * @param string $name 定数名
	 * @return string 定数
	 */
	public function getConstant (string $name) {
		if (!$this->constants) {
			$this->constants = new ConstantHandler(
				'PDO_' . $this['connection_name']
			);
		}
		return $this->constants[$name];
	}
}
