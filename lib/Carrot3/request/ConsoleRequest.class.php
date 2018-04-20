<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage request
 */

namespace Carrot3;

/**
 * コンソールリクエスト
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class ConsoleRequest extends Request {
	private $options;

	/**
	 * @access protected
	 */
	protected function __construct () {
		$this->options = Tuple::create();
		$this->addOption(Module::ACCESSOR);
		$this->addOption(Action::ACCESSOR);
		$this->addOption(Record::ACCESSOR);
		$this->parse();

		if (StringUtils::isBlank($this[Module::ACCESSOR])) {
			$this[Module::ACCESSOR] = 'Console';
		}
	}

	/**
	 * コマンドラインパーサオプションを追加
	 *
	 * @access public
	 * @param string $name オプション名
	 */
	public function addOption (string $name) {
		$this->options[$name] = [
			'name' => $name,
		];
	}

	/**
	 * コマンドラインをパース
	 *
	 * @access public
	 */
	public function parse () {
		$config = Tuple::create();
		foreach ($this->options as $option) {
			$config[] = $option['name'] . ':';
		}

		$this->clear();
		$this->setParameters(getopt($config->join('')));
		$this['id'] = $this[Record::ACCESSOR];
	}

	/**
	 * 出力内容を返す
	 *
	 * @access public
	 */
	public function getContents ():string {
		return null;
	}

	/**
	 * ヘッダ一式を返す
	 *
	 * @access public
	 * @return array ヘッダ一式
	 */
	public function getHeaders () {
		return null;
	}

	/**
	 * レンダラーを返す
	 *
	 * @access public
	 * @return Renderer レンダラー
	 */
	public function getRenderer () {
		return null;
	}

	/**
	 * UserAgentを返す
	 *
	 * @access public
	 * @return UserAgent リモートホストのUserAgent
	 */
	public function getUserAgent ():UserAgent {
		if (!$this->useragent) {
			$this->setUserAgent(UserAgent::create('Console'));
		}
		return $this->useragent;
	}

	/**
	 * 実際のUserAgentを返す
	 *
	 * エミュレート環境でも、実際のUserAgentを返す。
	 *
	 * @access public
	 * @return UserAgent リモートホストのUserAgent
	 */
	public function getRealUserAgent () {
		return $this->getUserAgent();
	}
}
