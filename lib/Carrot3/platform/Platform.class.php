<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage platform
 */

namespace Carrot3;

/**
 * 抽象プラットフォーム
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Platform extends ParameterHolder {

	/**
	 * @access protected
	 * @param string[] $params パラメータ配列
	 * @param string $uname uname文字列
	 */
	protected function __construct ($params) {
		$this->setParameters($params);
	}

	/**
	 * インスタンスを生成して返す
	 *
	 * @access public
	 * @param string $name プラットフォーム名
	 * @return Platform インスタンス
	 * @static
	 */
	static public function create ($name) {
		try {
			$class = Loader::getInstance()->getClass($name . 'Platform');
		} catch (\Exception $e) {
			$class = 'DefaultPlatform';
		}
		return new $class([
			'name' => $name,
			'version' => php_uname('r'),
		]);
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string プラットフォーム名
	 */
	public function getName () {
		return $this['name'];
	}

	/**
	 * 定数を取得して返す
	 *
	 * @access public
	 * @param ParameterHolder $keys キーの配列
	 * @param ConstantHandler $handler 定数ハンドラー
	 * @return Tuple 定数の配列
	 */
	public function getConstants (ParameterHolder $keys, ConstantHandler $handler = null) {
		if (!$handler) {
			$handler = new ConstantHandler;
		}
		$suffixes = Tuple::create([
			'_' . $this->getName(),
			'_default',
			null,
		]);

		$constants = Tuple::create();
		foreach ($keys as $key) {
			foreach ($suffixes as $suffix) {
				if (!StringUtils::isBlank($value = $handler[$key . $suffix])) {
					$constants[$key] = $value;
					break;
				}
			}
		}
		return $constants;
	}

	/**
	 * ファイルをリネーム
	 *
	 * @access public
	 * @param DirectoryEntry $file 対象ファイル
	 * @param string $path リネーム後のパス
	 */
	public function renameFile (DirectoryEntry $file, $path) {
		if (!rename($file->getPath(), $path)) {
			throw new FileException($this . 'を移動できません。');
		}
	}

	/**
	 * ファイルの内容から、メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function analyzeFile (File $file) {
		return rtrim(exec('file -b --mime-type ' . $file->getPath()));
	}

	/**
	 * ディレクトリを返す
	 *
	 * @access public
	 * @param string $name ディレクトリ名
	 * @return Directory ディレクトリ
	 */
	public function getDirectory ($name) {
		$constants = new ConstantHandler($name);
		foreach ([$this->getName(), 'default'] as $suffix) {
			if (!StringUtils::isBlank($path = $constants['dir_' . $suffix])) {
				return new Directory($path);
			}
		}
	}

	/**
	 * プロセスのオーナーを返す
	 *
	 * @access public
	 * @return string プロセスオーナーのユーザー名
	 */
	public function getProcessOwner () {
		$constants = new ConstantHandler('app_process');
		foreach ([$this->getName(), 'default'] as $suffix) {
			if (!StringUtils::isBlank($owner = $constants['uid_' . $suffix])) {
				return $owner;
			}
		}
	}
}

