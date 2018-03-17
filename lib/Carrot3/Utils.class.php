<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * ユーティリティ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Utils {

	/**
	 * @access private
	 */
	private function __construct () {
	}

	/**
	 * 絶対パスか？
	 *
	 * @access public
	 * @param string $path パス
	 * @return bool 絶対パスならTrue
	 * @static
	 */
	static public function isPathAbsolute ($path) {
		return !StringUtils::isBlank($path) && ($path[0] == '/');
	}

	/**
	 * ユニークなIDを生成して返す
	 *
	 * @access public
	 * @return string ユニークなID
	 * @static
	 */
	static public function getUniqueID () {
		return Crypt::digest([
			Date::create()->format('YmdHis'),
			uniqid(Numeric::getRandom(), true),
		]);
	}

	/**
	 * オブジェクトメソッドを実行
	 *
	 * @access public
	 * @param object $object オブジェクト
	 * @param string $method 関数名
	 * @param mixed $values 引数
	 * @return mixed メソッドの返値
	 * @static
	 */
	static public function executeMethod ($object, $method, $values = []) {
		if (is_string($object)) {
			$object = Loader::getInstance()->getClass($object);
		}
		if (!method_exists($object, $method)) {
			$message = new StringFormat('クラス "%s" のメソッド "%s" が未定義です。');
			$message[] = Utils::getClass($object);
			$message[] = $method;
			throw new \BadFunctionCallException($message);
		}
		return call_user_func_array([$object, $method], $values);
	}

	/**
	 * 名前空間を返す
	 *
	 * @access public
	 * @param mixed $class 完全修飾クラス名、又はオブジェクト
	 * @return string 名前空間
	 * @static
	 */
	static public function getNamespace ($class) {
		try {
			return (new \ReflectionClass($class))->getNamespaceName();
		} catch (\Exception $e) {
		}
	}

	/**
	 * 短いクラス名を返す
	 *
	 * @access public
	 * @param mixed $class 完全修飾クラス名、又はオブジェクト
	 * @return string 短いクラス名
	 * @static
	 */
	static public function getShortClass ($class) {
		try {
			return (new \ReflectionClass($class))->getShortName();
		} catch (\Exception $e) {
		}
	}

	/**
	 * クラス名を返す
	 *
	 * @access public
	 * @param mixed $class 完全修飾クラス名、又はオブジェクト
	 * @return string 完全修飾クラス名
	 * @static
	 */
	static public function getClass ($class) {
		try {
			return (new \ReflectionClass($class))->getName();
		} catch (\Exception $e) {
		}
	}

	/**
	 * クラス階層を返す
	 *
	 * @access public
	 * @param string $class 完全修飾クラス名、又はオブジェクト
	 * @return array クラス階層
	 * @static
	 */
	static public function getParentClasses ($class) {
		$classes = [];
		try {
			$class = new \ReflectionClass($class);
			do {
				$classes[] = $class->getName();
			} while ($class = $class->getParentClass());
		} catch (\Exception $e) {
		}
		return $classes;
	}
}
