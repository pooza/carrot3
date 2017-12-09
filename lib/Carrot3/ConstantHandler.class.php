<?php
/**
 * @package jp.co.b-shock.carrot3
 */

namespace Carrot3;

/**
 * 定数ハンドラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConstantHandler extends ParameterHolder implements Dictionary {
	use BasicObject;
	const PREFIX = 'BS';
	private $prefix;

	/**
	 * @access public
	 */
	public function __construct ($prefix = '') {
		$this->prefix = StringUtils::toUpper(rtrim($prefix, '_'));
	}

	/**
	 * パラメータを返す
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return mixed パラメータ
	 */
	public function getParameter ($name) {
		foreach ($this->createKeys($name) as $name) {
			if (defined($name)) {
				return constant($name);
			}
		}
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @param mixed $value 値
	 */
	public function setParameter ($name, $value) {
		if (defined($name = StringUtils::toUpper((string)$name))) {
			$message = new StringFormat('定数 "%s" は定義済みです。');
			$message[] = $name;
			throw new \BadFunctionCallException($message);
		}
		define($name, $value);
	}

	/**
	 * 全てのパラメータを返す
	 *
	 * @access public
	 * @return mixed[] 全てのパラメータ
	 */
	public function getParameters () {
		if (StringUtils::isBlank($this->prefix)) {
			return Tuple::create(get_defined_constants(true)['user']);
		} else {
			$pattern = '^' . self::PREFIX . '_' . $this->prefix;
			$constants = Tuple::create();
			foreach (get_defined_constants(true)['user'] as $key => $value) {
				if (mb_ereg($pattern, $key)) {
					$constants[$key] = $value;
				}
			}
			return $constants;
		}
	}

	/**
	 * パラメータが存在するか？
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return boolean 存在すればTrue
	 */
	public function hasParameter ($name) {
		foreach ($this->createKeys($name) as $name) {
			if (defined($name)) {
				return true;
			}
		}
		return false;
	}
	private function createKeys ($name) {
		$name = (string)$name;
		$keys = Tuple::create();
		if (StringUtils::isContain('::', $name)) {
			$keys[$name] = $name;
		} else {
			$key = StringUtils::toUpper($name);
			$keys[$key] = $key;
			foreach ([self::PREFIX, ''] as $prefix) {
				$key = Tuple::create();
				if (!StringUtils::isBlank($prefix)) {
					$key[] = self::PREFIX;
				}
				if (!StringUtils::isBlank($this->prefix)) {
					$key[] = $this->prefix;
				}
				$key[] = $name;
				$key = StringUtils::toUpper($key->join('_'));
				$keys[$key] = $key;
			}
		}
		return $keys;
	}

	/**
	 * パラメータを削除
	 *
	 * @access public
	 * @param string $name パラメータ名
	 */
	public function removeParameter ($name) {
		throw new \BadFunctionCallException('定数は削除できません。');
	}

	/**
	 * 翻訳して返す
	 *
	 * @access public
	 * @param string $label ラベル
	 * @param string $language 言語
	 * @return string 翻訳された文字列
	 */
	public function translate ($label, $language) {
		foreach ([null, '_' . $language] as $suffix) {
			if ($this->hasParameter($label . $suffix)) {
				if (StringUtils::isBlank($value = $this[$label . $suffix])) {
					return '';
				} else {
					return $value;
				}
			}
		}
	}

	/**
	 * 辞書の名前を返す
	 *
	 * @access public
	 * @return string 辞書の名前
	 */
	public function getDictionaryName () {
		return Utils::getClass($this);
	}
}
