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
abstract class MediaFile extends File implements Assignable {
	protected $output;

	/**
	 * @access public
	 * @param string $path パス
	 */
	public function __construct ($path) {
		$this->setPath($path);
		$this->attributes = Tuple::create();
		if ($this->getSerialized()) {
			$this->attributes->setParameters($this->getSerialized());
		} else if ($this->isExists()) {
			$this->serialize();
		}
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
		parent::analyze();
		$this->attributes['type'] = $this->analyzeType();

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
}
