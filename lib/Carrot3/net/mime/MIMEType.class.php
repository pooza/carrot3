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
	private $aliases;
	const DEFAULT_TYPE = 'application/octet-stream';

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->suffixes = Tuple::create();
		$this->aliases = Tuple::create();
		foreach (ConfigManager::getInstance()->compile('mime') as $entry) {
			$entry = Tuple::create($entry);
			if ($entry['suffixes']) {
				foreach ($entry['suffixes'] as $suffix) {
					$this['.' . ltrim($suffix, '.')] = $entry['type'];
					if ($this->suffixes->hasParameter($entry['type'])) {
						$this->suffixes[] = '.' . ltrim($suffix, '.');
					} else {
						$this->suffixes[$entry['type']] = '.' . ltrim($suffix, '.');
					}
				}
			}
			if ($entry['alias_to']) {
				$this->aliases[$entry['type']] = $entry['alias_to'];
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
	public function getParameter (?string $name) {
		return parent::getParameter('.' . ltrim(StringUtils::toLower($name), '.'));
	}

	/**
	 * パラメータを設定
	 *
	 * @access public
	 * @param string $name パラメータ名
	 * @param mixed $value 値
	 */
	public function setParameter (?string $name, $value) {
		if (!StringUtils::isBlank($value)) {
			$name = '.' . ltrim($name, '.');
			$name = StringUtils::toLower($name);
			parent::setParameter($name, $value);
		}
	}

	/**
	 * タイプのエイリアスを解決して返す
	 *
	 * @access public
	 * @param string $type タイプ
	 * @return string エイリアス解決後のタイプ
	 */
	public function resolveType ($type) {
		if (StringUtils::isBlank($type)) {
			return MIMEType::DEFAULT_TYPE;
		}
		if ($alias = $this->aliases[$type]) {
			return $alias;
		}
		return $type;
	}

	/**
	 * サフィックスを返す
	 *
	 * @access public
	 * @return Tuple サフィックス
	 */
	public function getSuffixes () {
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
	 * @param int $flags フラグのビット列
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
