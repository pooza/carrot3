<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime
 */

namespace Carrot3;

/**
 * MIMEタイプ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class MIMEType extends ParameterHolder {
	use Singleton;
	private $suffixes;
	const DEFAULT_TYPE = 'application/octet-stream';

	/**
	 * @access protected
	 */
	protected function __construct () {
		foreach (ConfigManager::getInstance()->compile('mime') as $entry) {
			foreach ($entry['suffixes'] as $suffix) {
				$this['.' . ltrim($suffix, '.')] = $entry['type'];
			}
		}
	}

	/**
	 * パラメータを返す
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @return mixed パラメータ
	 */
	public function getParameter ($name) {
		return parent::getParameter('.' . ltrim(StringUtils::toLower($name), '.'));
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @param mixed $value 値
	 */
	public function setParameter ($name, $value) {
		if (!StringUtils::isBlank($value)) {
			$name = '.' . ltrim($name, '.');
			$name = StringUtils::toLower($name);
			parent::setParameter($name, $value);
		}
	}

	/**
	 * サフィックスを返す
	 *
	 * @access public
	 * @return Tuple サフィックス
	 */
	public function getSuffixes () {
		if (!$this->suffixes) {
			$this->suffixes = Tuple::create();
			foreach (ConfigManager::getInstance()->compile('mime') as $entry) {
				foreach ($entry['suffixes'] as $suffix) {
					if ($this->suffixes->hasParameter($entry['type'])) {
						continue;
					}
					$this->suffixes[$entry['type']] = '.' . ltrim($suffix, '.');
				}
			}
		}
		return $this->suffixes;
	}

	/**
	 * 非推奨含め、全てのサフィックスを返す
	 *
	 * @access public
	 * @return Tuple 全てのサフィックス
	 */
	public function getAllSuffixes () {
		$suffixes = Tuple::create();
		foreach ($this->params as $key => $value) {
			$suffixes[] = $key;
		}
		return $suffixes;
	}

	/**
	 * 規定のメディアタイプを返す
	 *
	 * @access public
	 * @param string $suffix サフィックス、又はファイル名
	 * @param integer $flags フラグのビット列
	 *   MIMEUtils::IGNORE_INVALID_TYPE タイプが不正ならapplication/octet-streamを返す
	 * @return string メディアタイプ
	 * @static
	 */
	static public function getType ($suffix, $flags = MIMEUtils::IGNORE_INVALID_TYPE) {
		if ($type = self::getInstance()[MIMEUtils::getFileNameSuffix($suffix)]) {
			return $type;
		} else if ($flags & MIMEUtils::IGNORE_INVALID_TYPE) {
			return self::DEFAULT_TYPE;
		}
	}

	/**
	 * 規定のサフィックスを返す
	 *
	 * @access public
	 * @param string $type MIMEタイプ
	 * @return string サフィックス
	 * @static
	 */
	static public function getSuffix ($type) {
		return self::getInstance()->getSuffixes()[$type];
	}
}

