<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage config.compiler
 */

namespace Carrot3;

/**
 * 抽象設定コンパイラ
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
abstract class ConfigCompiler extends ParameterHolder {
	use BasicObject;
	private $body;

	/**
	 * @access public
	 * @param iterable $params パラメータ
	 */
	public function __construct (iterable $params = []) {
		$this->initialize($params);
	}

	/**
	 * 初期化
	 *
	 * @access public
	 * @param iterable $params パラメータ
	 * @return bool
	 */
	public function initialize (iterable $params = []):bool {
		$this->setParameters($params);
		return true;
	}

	/**
	 * 実行
	 *
	 * @access public
	 * @param ConfigFile $file 設定ファイル
	 * @abstract
	 */
	abstract public function execute (ConfigFile $file);

	/**
	 * コンパイル後のphpステートメントを返す
	 *
	 * @access protected
	 * @return string コンパイル結果
	 */
	protected function getBody () {
		return $this->body->join("\n");
	}

	/**
	 * phpステートメントを初期化
	 *
	 * @access protected
	 */
	protected function clearBody () {
		$this->body = Tuple::create();
		$this->putLine('<?php');
		$this->putLine('// auth-generated by ' . Utils::getClass($this));
		$this->putLine('// date: ' . date('Y/m/d H:i:s'));
	}

	/**
	 * phpステートメントの末尾に1行追加
	 *
	 * @access public
	 * @param mixed $line phpステートメント
	 */
	protected function putLine ($line) {
		if ($line instanceof MessageContainer) {
			$line = $line->getMessage();
		}
		$this->body[] = $line;
	}

	/**
	 * 文字列のクォート
	 *
	 * @access public
	 * @param string $value 置換対象
	 * @return string 置換結果
	 * @static
	 */
	static public function quote ($value) {
		if (is_iterable($value)) {
			$body = Tuple::create();
			foreach ($value as $key => $item) {
				$body[] = sprintf('%s => %s', self::quote($key), self::quote($item));
			}
			return sprintf('[%s]', $body->join(', '));
		} else {
			$value = trim($value);
			switch (StringUtils::toLower($value)) {
				case null:
					return 'null';
				case 'on':
				case 'yes':
				case 'true':
					return 'true';
				case 'off':
				case 'no':
				case 'false':
					return 'false';
				default:
					if (is_numeric($value) && !mb_ereg('^0', $value)) {
						return $value;
					} else {
						$value = str_replace("\\", "\\\\", $value);
						$value = str_replace("%'", "\"", $value);
						$value = str_replace("'", "\\'", $value);
						return "'" . $value . "'";
					}
			}
		}
	}

	/**
	 * 定数で置換
	 *
	 * @access protected
	 * @param string $value 置換対象
	 * @return string 置換結果
	 * @static
	 */
	static protected function replaceConstants ($value) {
		$value = str_replace('%%', '##PERCENT##', $value);
		$constants = new ConstantHandler;
		foreach (StringUtils::eregMatchAll('%([_[:alnum:]]+)%', $value) as $matches) {
			$value = str_replace($matches[0], $constants[$matches[1]], $value);
		}
		$value = str_replace('##PERCENT##', '%', $value);
		return $value;
	}

	/**
	 * 設定ファイルをパース
	 *
	 * @access public
	 * @param string $name フォルダ名
	 * @return Tuple
	 * @static
	 */
	static public function parseFiles (string $name) {
		$manager = ConfigManager::getInstance();
		$values = Tuple::create();
		$values->setParameters($manager->compile($name . '/carrot'));
		$values->setParameters($manager->compile($name . '/application'));
		return $values;
	}
}
