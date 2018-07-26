<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage net.mime.header
 */

namespace Carrot3;

/**
 * 基底ヘッダ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
class MIMEHeader extends ParameterHolder {
	use BasicObject;
	protected $part;
	protected $name;
	protected $contents;
	const WITHOUT_CRLF = 1;

	/**
	 * @access protected
	 */
	protected function __construct () {
	}

	/**
	 * パートを返す
	 *
	 * @access public
	 * @return MIMEDocument メールパート
	 */
	public function getPart () {
		return $this->part;
	}

	/**
	 * パートを設定
	 *
	 * @access public
	 * @param MIMEDocument $part メールパート
	 */
	public function setPart (MIMEDocument $part) {
		$this->part = $part;
	}

	/**
	 * インスタンスを生成して返す
	 *
	 * @access public
	 * @param string $name ヘッダ名
	 * @return MIMEHeader ヘッダ
	 */
	static public function create (string $name) {
		$name = self::capitalize($name);
		try {
			$loader = Loader::getInstance();
			$class = $loader->getClass(str_replace('-', '', $name) . 'MIMEHeader');
		} catch (\Throwable $e) {
			$class = $loader->getClass('MIMEHeader');
		}
		$header = new $class;
		$header->setName($name);
		return $header;
	}

	static private function capitalize (string $name) {
		$name = StringUtils::stripControlCharacters($name);
		$name = StringUtils::explode('-', $name);
		$name = StringUtils::capitalize($name);
		return $name->join('-');
	}

	/**
	 * 名前を返す
	 *
	 * @access public
	 * @return string ヘッダ名
	 */
	public function getName ():string {
		return $this->name;
	}

	/**
	 * 名前を設定
	 *
	 * @access public
	 * @param string $name ヘッダ名
	 */
	public function setName (string $name) {
		$this->name = $name;
	}

	/**
	 * 実体を返す
	 *
	 * @access public
	 * @return mixed 実体
	 */
	public function getEntity () {
		return $this->contents;
	}

	/**
	 * 内容を返す
	 *
	 * @access public
	 * @return string 内容
	 */
	public function getContents ():string {
		return $this->contents;
	}

	/**
	 * 内容を設定
	 *
	 * @access public
	 * @param mixed $contents 内容
	 */
	public function setContents ($contents) {
		if ($contents instanceof Renderer) {
			$contents = $contents->getContents();
		}
		$contents = StringUtils::stripControlCharacters($contents);
		$this->contents = MIMEUtils::decode($contents);
		$this->parse();
	}

	/**
	 * 内容を追加
	 *
	 * @access public
	 * @param string $contents 内容
	 */
	public function appendContents ($contents) {
		$contents = StringUtils::stripControlCharacters($contents);
		$contents = MIMEUtils::decode($contents);
		if (StringUtils::getEncoding($this->contents . $contents) == 'ascii') {
			$contents = ' ' . $contents;
		}
		$this->contents .= $contents;
		$this->parse();
	}

	/**
	 * ヘッダの内容からパラメータを抜き出す
	 *
	 * @access protected
	 */
	protected function parse () {
		foreach (StringUtils::explode(';', $this->contents) as $index => $param) {
			if ($index == 0) {
				$this[0] = trim($param);
			}
			if (mb_ereg('^ *([-[:alpha:]]+)="?([^";]+)"?', $param, $matches)) {
				$this[StringUtils::toLower($matches[1])] = $matches[2];
			}
		}
	}

	/**
	 * 改行などの整形を行うか？
	 *
	 * @access protected
	 * @return bool 整形を行うならTrue
	 */
	protected function isFormattable ():bool {
		return true;
	}

	/**
	 * ヘッダを整形して返す
	 *
	 * @access public
	 * @param int $flags フラグのビット列
	 *   self::WITHOUT_CRLF 改行を含まない
	 * @return ヘッダ行
	 */
	public function format (int $flags = 0) {
		if (!$this->isVisible()) {
			return null;
		}
		if (!$this->isFormattable() || ($flags & self::WITHOUT_CRLF)) {
			return $this->name . ': ' . $this->getContents();
		}

		$contents = $this->name . ': ' . MIMEUtils::encode($this->getContents());
		$contents = StringUtils::explode(MIMEUtils::ENCODE_PREFIX, $contents);
		$glue = MIMEDocument::LINE_SEPARATOR . ' ' . MIMEUtils::ENCODE_PREFIX;
		$contents = $contents->join($glue) . MIMEDocument::LINE_SEPARATOR;
		return $contents;
	}

	/**
	 * 可視か？
	 *
	 * @access public
	 * @return bool 可視ならばTrue
	 */
	public function isVisible ():bool {
		return !StringUtils::isBlank($this->getContents());
	}

	/**
	 * キャッシュ可能か？
	 *
	 * @access public
	 * @return bool キャッシュ可能ならばTrue
	 */
	public function isCacheable ():bool {
		return true;
	}

	/**
	 * 複数行を許容するか？
	 *
	 * @access public
	 * @return bool 許容ならばTrue
	 */
	public function isMultiple ():bool {
		return false;
	}
}
