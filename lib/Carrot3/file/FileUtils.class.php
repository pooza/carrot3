<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ファイルユーティリティ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class FileUtils {

	/**
	 * @access private
	 */
	private function __construct () {
	}

	/**
	 * 特別なディレクトリを返す
	 *
	 * @access public
	 * @param string $name ディレクトリの名前
	 * @return Directory ディレクトリ
	 * @static
	 */
	static public function getDirectory ($name) {
		return DirectoryLayout::getInstance()[$name];
	}

	/**
	 * 特別なディレクトリのパスを返す
	 *
	 * @access public
	 * @param string $name ディレクトリの名前
	 * @return string パス
	 * @static
	 */
	static public function getPath ($name) {
		if ($dir = self::getDirectory($name)) {
			return $dir->getPath();
		}
	}

	/**
	 * 特別なディレクトリのURLを返す
	 *
	 * @access public
	 * @param string $name ディレクトリの名前
	 * @return HTTPURL URL
	 * @static
	 */
	static public function createURL ($name, $path = '') {
		if (self::getDirectory($name)) {
			$url = DirectoryLayout::getInstance()->createURL($name);
			$url['path'] .= $path;
			return $url;
		}
	}

	/**
	 * 拡張子を返す
	 *
	 * @access public
	 * @param string $name ファイル名、またはパス
	 * @return string 拡張子
	 * @static
	 */
	static public function getSuffix ($name) {
		$parts = StringUtils::explode('.', $name);
		if (1 < $parts->count()) {
			return '.' . $parts->getIterator()->getLast();
		}
	}

	/**
	 * ファイル名の拡張子から、規定のMIMEタイプを返す
	 *
	 * MIMEType::getTypeのエイリアス
	 *
	 * @access public
	 * @param string $name ファイル名、またはパス
	 * @return string MIMEタイプ
	 * @static
	 */
	static public function getDefaultType ($name) {
		return MIMEType::getType($name);
	}

	/**
	 * 名前がドットから始まるか？
	 *
	 * @access public
	 * @param string $name ファイル名、またはパス
	 * @return boolean ドットから始まるならTrue
	 * @static
	 */
	static public function isDottedName ($name) {
		return mb_ereg('^\\.', basename($name));
	}

	/**
	 * 一時ファイルを生成して返す
	 *
	 * @access public
	 * @param string $suffix 拡張子
	 * @param string $class クラス名
	 * @return File 一時ファイル
	 * @static
	 */
	static public function createTemporaryFile ($suffix = null, $class = 'Carrot3\\File') {
		if (StringUtils::isBlank($suffix)) {
			$name = Utils::getUniqueID();
		} else {
			$name = Utils::getUniqueID() . '.' . ltrim($suffix, '.');
		}
		if (!$file = FileUtils::getDirectory('tmp')->createEntry($name, $class)) {
			throw new FileException('一時ファイルが生成できません。');
		}
		return $file;
	}

	/**
	 * 一時ディレクトリを生成して返す
	 *
	 * @access public
	 * @param string $class クラス名
	 * @return Directory 一時ディレクトリ
	 * @static
	 */
	static public function createTemporaryDirectory ($class = 'Carrot3\\Directory') {
		$name = Utils::getUniqueID();
		if (!$dir = FileUtils::getDirectory('tmp')->createDirectory($name, $class)) {
			throw new FileException('一時ディレクトリが生成できません。');
		}
		return $dir;
	}
}

