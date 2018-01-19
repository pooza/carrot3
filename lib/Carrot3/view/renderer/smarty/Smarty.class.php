<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage view.renderer.smarty
 */

namespace Carrot3;
require_once BS_LIB_DIR . '/Smarty/Smarty.class.php';

/**
 * Smartyラッパー
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class Smarty extends \Smarty implements TextRenderer {
	use BasicObject;
	private $type;
	private $encoding;
	private $template;
	private $error;
	private $useragent;
	private $headers;
	private $compiler;
	private $finder;
	public $compiler_class = 'SmartyCompiler';

	/**
	 * @access public
	 */
	public function __construct() {
		$this->finder = new FileFinder('TemplateFile');
		$this->finder->clearDirectories();
		$this->compile_dir = FileUtils::getPath('compile');
		$this->plugins_dir = [];
		$this->plugins_dir[] = FileUtils::getPath('local_lib') . '/smarty';
		$this->plugins_dir[] = FileUtils::getPath('carrot') . '/view/renderer/smarty/plugins';
		$this->plugins_dir[] = FileUtils::getPath('lib') . '/Smarty/plugins';
		$this->force_compile = BS_DEBUG;
		$this->error_reporting = E_ALL ^ E_NOTICE;
		$this->registerDirectory(FileUtils::getDirectory('templates'));
		$this->setEncoding('utf-8');
		$this->setUserAgent($this->request->getUserAgent());
		$this->setAttribute('is_debug', BS_DEBUG);
	}

	/**
	 * テンプレートディレクトリをクリア
	 *
	 * @access public
	 */
	public function clearDirectories () {
		$this->finder->clearDirectories();
		$this->template_dir = '';
	}

	/**
	 * テンプレートディレクトリを設定
	 *
	 * @access public
	 * @param Directory $dir テンプレートディレクトリ
	 */
	public function registerDirectory (Directory $dir) {
		$dir->setDefaultSuffix('.tpl');
		$this->finder->registerDirectory($dir);
		$this->template_dir = $dir->getPath();
	}

	/**
	 * 規定の修飾子を追加
	 *
	 * @access public
	 * @param string $name 修飾子の名前
	 */
	public function addModifier ($name) {
		$this->default_modifiers[] = $name;
	}

	/**
	 * 規定の修飾子をクリア
	 *
	 * @access public
	 * @param string $name 修飾子の名前
	 */
	public function clearModifier () {
		$this->default_modifiers = [];
	}

	/**
	 * 出力フィルタを追加
	 *
	 * @access public
	 * @param string $name 出力フィルタの名前
	 */
	public function addOutputFilter ($name) {
		$this->load_filter('output', $name);
	}

	/**
	 * 送信内容を返す
	 *
	 * @access public
	 * @return string 送信内容
	 */
	public function getContents () {
		if (!$template = $this->getTemplate()) {
			throw new ViewException('テンプレートが未定義です。');
		}
		$this->setAttribute('useragent', $this->getUserAgent());
		return $template->compile();
	}

	/**
	 * 送信内容を返す
	 *
	 * getContentsのエイリアス
	 *
	 * @access public
	 * @return string 送信内容
	 * @final
	 */
	final public function render () {
		return $this->getContents();
	}

	/**
	 * 出力内容のサイズを返す
	 *
	 * @access public
	 * @return integer サイズ
	 */
	public function getSize () {
		return strlen($this->getContents());
	}

	/**
	 * ヘッダ一式を返す
	 *
	 * @access public
	 * @return string[] ヘッダ一式
	 */
	public function getHeaders () {
		if (!$this->headers) {
			$this->headers = Tuple::create();
		}
		return $this->headers;
	}

	/**
	 * 対象UserAgentを返す
	 *
	 * @access public
	 * @return UserAgent 対象UserAgent
	 */
	public function getUserAgent () {
		return $this->useragent;
	}

	/**
	 * 対象UserAgentを設定
	 *
	 * @access public
	 * @param UserAgent $useragent 対象UserAgent
	 */
	public function setUserAgent (UserAgent $useragent) {
		$this->useragent = $useragent;
		$this->setAttribute('useragent', null);
		$this->setEncoding($useragent->getDefaultEncoding());

		$this->finder->clearSuffixes();
		if ($useragent->isMobile()) {
			$this->finder->registerSuffix('mobile');
		}
		if ($useragent->isSmartPhone()) {
			$this->finder->registerSuffix('smartphone');
		}
		$this->finder->registerSuffix($useragent->getType());
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		if (!$this->type) {
			$this->type = MIMEType::getType('html');
		}
		return $this->type;
	}

	/**
	 * メディアタイプを設定
	 *
	 * @access public
	 * @param string $type メディアタイプ
	 */
	public function setType ($type) {
		$this->type = $type;
	}

	/**
	 * エンコードを返す
	 *
	 * @access public
	 * @return string PHPのエンコード名
	 */
	public function getEncoding () {
		return $this->encoding;
	}

	/**
	 * エンコードを設定
	 *
	 * @access public
	 * @param string $encoding PHPのエンコード名
	 */
	public function setEncoding ($encoding) {
		if (StringUtils::isBlank(mb_preferred_mime_name($encoding))) {
			throw new ViewException('利用できないエンコード名です。');
		}
		$this->encoding = $encoding;
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return boolean 出力可能ならTrue
	 */
	public function validate () {
		if (!$this->getTemplate()) {
			$this->error = 'テンプレートが未定義です。';
			return false;
		}
		return true;
	}

	/**
	 * エラーメッセージを返す
	 *
	 * @access public
	 * @return string エラーメッセージ
	 */
	public function getError () {
		return $this->error;
	}

	/**
	 * テンプレートファイルを返す
	 *
	 * @access public
	 * @return TemplateFile テンプレートファイル
	 */
	public function getTemplate () {
		return $this->template;
	}

	/**
	 * テンプレートを設定
	 *
	 * @access public
	 * @param mixied $template テンプレートファイル名、又はテンプレートファイル
	 */
	public function setTemplate ($template) {
		if (!$file = $this->searchTemplate($template)) {
			$message = new StringFormat('テンプレート "%s" が見つかりません。');
			$message[] = $template;
			throw new ViewException($message);
		}
		$this->template = $file;
		$this->template->setEngine($this);
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return mixed 属性
	 */
	public function getAttribute ($name) {
		return $this->get_template_vars($name);
	}

	/**
	 * 全ての属性を返す
	 *
	 * @access public
	 * @return mixed[] 全ての属性
	 */
	public function getAttributes () {
		return $this->get_template_vars();
	}

	/**
	 * 属性を設定
	 *
	 * @access public
	 * @param string $name 属性名
	 * @param mixed $value 属性値
	 */
	public function setAttribute ($name, $value) {
		if ($value instanceof Assignable) {
			$this->assign((string)$name, $value->assign());
		} else if (!StringUtils::isBlank($value)) {
			$this->assign((string)$name, $value);
		}
	}

	/**
	 * 属性をまとめて設定
	 *
	 * @access public
	 * @param mixed[] $attribures 属性値
	 */
	public function setAttributes ($attributes) {
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}

	/**
	 * ファイル名から実テンプレートファイルを返す
	 *
	 * @access public
	 * @param string $name ファイル名
	 * @return TemplateFile 実テンプレートファイル
	 */
	public function searchTemplate ($name) {
		return $this->finder->execute($name);
	}

	/**
	 * コンパイラを返す
	 *
	 * @return SmartyCompiler コンパイラ
	 */
	public function getCompiler () {
		if (!$this->compiler) {
			$class = $this->loader->getClass($this->compiler_class);
			$this->compiler = new $class;
			if (!$this->compiler->initialize($this)) {
				$message = new StringFormat('%sが初期化できません。');
				$message[] = $this->compiler_class;
				throw new ViewException($message);
			}
		}
		return $this->compiler;
	}

	/**
	 * クォートされた文字列から、クォートを外す
	 *
	 * @access public
	 * @param mixed $value 変換対象の文字列又は配列
	 * @return mixed 変換後
	 */
	public function _dequote ($value) {
		return StringUtils::dequote($value);
	}

	/**
	 * load a filter of specified type and name
	 *
	 * @param string $type filter type
	 * @param string $name filter name
	 */
	function load_filter ($type, $name) {
		switch ($type) {
			case 'output':
				require_once SMARTY_CORE_DIR . 'core.load_plugins.php';
				smarty_core_load_plugins(
					['plugins' => [[$type . 'filter', $name, null, null, false]]],
					$this
				);
				break;
			case 'pre':
			case 'post':
				if (!isset($this->_plugins[$type . 'filter'][$name])) {
					$this->_plugins[$type . 'filter'][$name] = false;
				}
				break;
		}
	}

	/**
	 * リソースを指定してコンパイル
	 *
	 * @param string $resource リソース名
	 * @param string $source ソース文字列
	 * @param string $compiled コンパイル済み文字列
	 * @param string $path コンパイル済みテンプレートへのパス
	 * @return boolean 成功ならTrue
	 */
	public function _compile_source ($resource, &$source, &$compiled, $path = null) {
		$compiler = $this->getCompiler();

		if (isset($path) && isset($this->_cache_serials[$path])) {
			$compiler->setAttribute('_cache_serial', $this->_cache_serials[$path]);
		}
		$compiler->setAttribute('_cache_include', $path);
		$result = $compiler->_compile_file($resource, $source, $compiled);

		if ($compiler->_cache_serial) {
			$this->_cache_include_info = [
				'cache_serial' => $compiler->getAttribute('_cache_serial'),
				'plugins_code' => $compiler->getAttribute('_plugins_code'),
				'include_file_path' => $path,
			];
		} else {
			$this->_cache_include_info = null;
		}
		return $result;
	}

	/**
	 * includeタグの拡張
	 *
	 * @access public
	 * @param mixed[] $params パラメータ一式
	 */
	public function _smarty_include ($params) {
		$template =& $params['smarty_include_tpl_file'];
		if ($file = $this->searchTemplate($template)) {
			$template = $file->getPath();
			return parent::_smarty_include($params);
		}

		$message = new StringFormat('テンプレート "%s"が見つかりません。');
		$message[] = $template;
		throw new ViewException($message);
	}

	/**
	 * エラートリガ
	 *
	 * @access public
	 * @param string $error_msg エラーメッセージ
	 * @param integer $error_type
	 */
	public function trigger_error ($error_msg, $error_type = null) {
		throw new ViewException($error_msg);
	}

	/**
	 * コンパイル先ファイル名を返す
	 *
	 * @access public
	 * @param string $base コンパイルディレクトリ
	 * @param string $source ソーステンプレート名
	 * @param string $id
	 * @return string
	 */
	public function _get_auto_filename ($base, $source = null, $id = null) {
		if (!Utils::isPathAbsolute($source)) {
			$message = new StringFormat('テンプレート名 "%s" はフルパスではありません。');
			$message[] = $source;
			throw new ViewException($message);
		}
		$source = str_replace(BS_ROOT_DIR, '', $source);
		$source = str_replace('/', '%', $source);
		return $base . '/' . $source;
	}
}
