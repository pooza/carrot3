<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage media
 */

namespace Carrot3;

/**
 * メディアファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class MediaFile extends File implements \ArrayAccess, Assignable {
	protected $attributes;
	protected $output;
	protected $types;

	/**
	 * @access public
	 * @param string $path パス
	 */
	public function __construct ($path) {
		$this->setPath($path);
		$this->attributes = Tuple::create();
		if ($serialized = $this->getSerialized()) {
			$this->attributes->setParameters($serialized);
		} else if ($this->isExists()) {
			$this->analyze();
			$this->serialize();
		}
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return mixed 属性
	 */
	public function getAttribute ($name) {
		return $this->attributes[$name];
	}

	/**
	 * 全ての属性を返す
	 *
	 * @access public
	 * @return Tuple 全ての属性
	 */
	public function getAttributes () {
		return $this->attributes;
	}

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return boolean バイナリファイルならTrue
	 */
	public function isBinary () {
		return true;
	}

	/**
	 * ファイルを解析
	 *
	 * @access protected
	 */
	protected function analyze () {
		$command = $this->createCommand();
		$command->push('-i', null);
		$command->push($this->getPath());
		$this->output = $command->getResult()->join("\n");

		if (mb_ereg('Error .*$', $this->output, $matches)) {
			$this->attributes['type'] = MIMEType::DEFAULT_TYPE;
			$this->error = $matches[0];
			return;
		}

		if (mb_ereg('Duration: ([.:[:digit:]]+),', $this->output, $matches)) {
			$this->attributes['duration'] = $matches[1];
			$sec = StringUtils::explode(':', $matches[1]);
			$this->attributes['seconds'] = ($sec[0] * 3600) + ($sec[1] * 60) + $sec[2];
		}
		$this->attributes['path'] = $this->getPath();
		$this->attributes['name'] = $this->getName();
		$this->attributes['type'] = $this->analyzeType();
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		if (StringUtils::isBlank($this->attributes['type'])) {
			$this->analyze();
		}
		return $this->attributes['type'];
	}

	/**
	 * 表示用のHTML要素を返す
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @param UserAgent $useragent 対象ブラウザ
	 * @return DivisionElement 要素
	 * @abstract
	 */
	abstract public function createElement (ParameterHolder $params, UserAgent $useragent = null);

	/**
	 * 幅でリサイズ
	 *
	 * @access public
	 * @param ParameterHolder $params パラメータ配列
	 * @param UserAgent $useragent 対象ブラウザ
	 * @return ParameterHolder リサイズ後のパラメータ配列
	 */
	public function resizeByWidth (ParameterHolder $params, UserAgent $useragent = null) {
		if (!$params[__FUNCTION__]) {
			if (!$useragent) {
				$useragent = $this->request->getUserAgent();
			}

			$info = $useragent->getDisplayInfo();
			if (!$params['max_width'] && $info['width']) {
				$params['max_width'] = $info['width'];
			}

			if ($params['max_width'] && ($params['max_width'] < $params['width'])) {
				$params['height'] = Numeric::round(
					$params['height'] / $params['width'] * $params['max_width']
				);
				$params['width'] = $params['max_width'];
			}
			$params[__FUNCTION__] = true;
		}
		return $params;
	}

	/**
	 * スタイル属性を返す
	 *
	 * @access protected
	 * @param ParameterHolder $params パラメータ配列
	 * @return CSSSelector スタイル属性
	 */
	protected function getStyles (ParameterHolder $params) {
		$style = new CSSSelector;
		if ($params['width']) {
			$style['width'] = $params['width'] . 'px';
		} else {
			$style['width'] = $this['width'] . 'px';
		}
		if ($params['height']) {
			$style['height'] = $params['height'] . 'px';
		} else {
			$style['height'] = $this['height_full'] . 'px';
		}
		return $style;
	}

	/**
	 * メディアURLを返す
	 *
	 * @access protected
	 * @param ParameterHolder $params パラメータ配列
	 * @return URL メディアURL
	 */
	protected function createURL (ParameterHolder $params) {
		$url = URL::create($params['href_prefix']);
		$url['path'] .= $this->getName() . $params['href_suffix'];
		if ($this->user->isAdministrator()) {
			$url->setParameter('at', Numeric::getRandom(1000, 9999));
		}
		return $url;
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return boolean 出力可能ならTrue
	 */
	public function validate () {
		if (!$this->attributes->count()) {
			$this->analyze();
		}
		return $this->isReadable() && $this->attributes->count();
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return boolean 要素が存在すればTrue
	 */
	public function offsetExists ($key) {
		return $this->attributes->hasParameter($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return mixed 要素
	 */
	public function offsetGet ($key) {
		return $this->getAttribute($key);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @param mixed 要素
	 */
	public function offsetSet ($key, $value) {
		throw new MediaException($this . 'の属性を設定できません。');
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		throw new MediaException($this . 'の属性を削除できません。');
	}

	/**
	 * コマンドラインを返す
	 *
	 * @access public
	 * @return CommandLine コマンドライン
	 */
	public function createCommand () {
		$command = new CommandLine('bin/ffmpeg');
		$command->setDirectory(FileUtils::getDirectory('ffmpeg'));
		$command->setStderrRedirectable();
		return $command;
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		if (!$this->attributes->count()) {
			$this->analyze();
		}
		$this->controller->setAttribute($this, $this->attributes);
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return mixed アサインすべき値
	 */
	public function assign () {
		return $this->getSerialized();
	}

	/**
	 * 探す
	 *
	 * @access public
	 * @param mixed $file パラメータ配列、File、ファイルパス文字列
	 * @param string $class クラス名
	 * @return File ファイル
	 * @static
	 */
	static public function search ($file, $class = 'File') {
		$files = new FileFinder($class);
		if (is_array($file) || ($file instanceof ParameterHolder)) {
			$params = Tuple::create($file);
			if (StringUtils::isBlank($path = $params['src'])) {
				$records = new RecordFinder($params);
				if ($record = $records->execute()) {
					if ($attachment = $record->getAttachment($params['size'])) {
						return $files->execute($attachment);
					}
				}
			} else {
				return $files->execute($path);
			}
		} else {
			return $files->execute($file);
		}
	}
}

