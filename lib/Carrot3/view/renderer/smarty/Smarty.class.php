<?php
namespace Carrot3;
require_once BS_LIB_DIR . '/Smarty/Smarty.class.php';

class Smarty extends \Smarty implements TextRenderer {
	use BasicObject, KeyGenerator;
	private $type;
	private $encoding;
	private $template;
	private $error;
	private $useragent;
	private $headers;
	private $compiler;
	private $finder;
	public $compiler_class = 'SmartyCompiler';

	public function __construct() {
		$this->finder = new FileFinder('TemplateFile');
		$this->finder->clearDirectories();
		$this->compile_dir = FileUtils::getPath('compile');
		$this->plugins_dir = [
			FileUtils::getPath('local_lib') . '/smarty',
			FileUtils::getPath('carrot') . '/view/renderer/smarty/plugins',
			FileUtils::getPath('lib') . '/Smarty/plugins',
		];
		$this->force_compile = true;
		$this->error_reporting = E_ALL ^ E_NOTICE;
		$this->registerDirectory(FileUtils::getDirectory('templates'));
		$this->setEncoding('utf-8');
		$this->setUserAgent($this->request->getUserAgent());
		$this->setAttribute('is_debug', BS_DEBUG);
	}

	public function clearDirectories () {
		$this->finder->clearDirectories();
		$this->template_dir = '';
	}

	public function registerDirectory (Directory $dir) {
		$dir->setDefaultSuffix('.tpl');
		$this->finder->registerDirectory($dir);
		$this->template_dir = $dir->getPath();
	}

	public function addModifier ($name) {
		$this->default_modifiers[] = $name;
	}

	public function clearModifier () {
		$this->default_modifiers = [];
	}

	public function addOutputFilter ($name) {
		$this->load_filter('output', $name);
	}

	public function getContents ():string {
		if (!$template = $this->getTemplate()) {
			throw new ViewException('テンプレートが未定義です。');
		}
		$this->setAttribute('useragent', $this->getUserAgent());
		return $template->compile();
	}

	final public function render () {
		return $this->getContents();
	}

	public function getSize ():int {
		return strlen($this->getContents());
	}

	public function getHeaders () {
		if (!$this->headers) {
			$this->headers = Tuple::create();
		}
		return $this->headers;
	}

	public function getUserAgent ():UserAgent {
		return $this->useragent;
	}

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

	public function getType ():string {
		if (!$this->type) {
			$this->type = MIMEType::getType('html');
		}
		return $this->type;
	}

	public function setType (string $type) {
		$this->type = $type;
	}

	public function fetch ($resource, $cache_id = null, $compile_id = null, $display = false) {
		$template = $this->searchTemplate($resource);
		$key = $this->createKey([
			$template->getContents(),
			$this->getAttributes(),
			BS_DEBUG,
			$this->user->isAdministrator(),
			$this->user->isAuthor(),
		]);

		$serials = new SerializeHandler;
		$serials->getStorage()->select((int)BS_REDIS_DATABASES_RENDER);
		$serials->setConfig('template_cache_ttl', BS_VIEW_TEMPLATE_CACHE_TTL);
		if (StringUtils::isBlank($contents = $serials[$key])) {
			$this->getCompiler()->_compile_file(
				$template->getPath(),
				$template->getContents(),
				$compiled
			);
			ob_start();
			eval(mb_ereg_replace('^\s*\<\?php', '', $compiled));
			$contents = ob_get_contents();
			ob_end_clean();
			foreach ((array)$this->_plugins['outputfilter'] as $filter) {
				$contents = call_user_func_array($filter[0], [$contents, &$this]);
			}
			$serials[$key] = $contents;
		}
		return $contents;
	}

	public function getEncoding ():string {
		return $this->encoding;
	}

	public function setEncoding (string $encoding) {
		if (StringUtils::isBlank(mb_preferred_mime_name($encoding))) {
			throw new ViewException('利用できないエンコード名です。');
		}
		$this->encoding = $encoding;
	}

	public function validate ():bool {
		if (!$this->getTemplate()) {
			$this->error = 'テンプレートが未定義です。';
			return false;
		}
		return true;
	}

	public function getError ():?string {
		return $this->error;
	}

	public function getTemplate () {
		return $this->template;
	}

	public function setTemplate ($template) {
		if (!$file = $this->searchTemplate($template)) {
			$message = new StringFormat('テンプレート "%s" が見つかりません。');
			$message[] = $template;
			throw new ViewException($message);
		}
		$this->template = $file;
		$this->template->setEngine($this);
	}

	public function getAttribute (string $name) {
		return $this->get_template_vars($name);
	}

	public function getAttributes ():Tuple {
		return Tuple::create($this->get_template_vars());
	}

	public function setAttribute (string $name, $value) {
		if ($value instanceof Assignable) {
			$this->assign($name, $value->assign());
		} else if (!StringUtils::isBlank($value)) {
			$this->assign($name, $value);
		}
	}

	public function setAttributes (iterable $attributes) {
		foreach ($attributes as $key => $value) {
			$this->setAttribute($key, $value);
		}
	}

	public function searchTemplate ($name) {
		return $this->finder->execute($name);
	}

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

	public function _dequote ($value) {
		return StringUtils::dequote($value);
	}

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

	public function _compile_source ($resource, &$source, &$compiled, $path = null) {
		return $this->getCompiler()->_compile_file($resource, $source, $compiled);
	}

	public function _smarty_include ($params) {
		$template = $params['smarty_include_tpl_file'];
		if (!$file = $this->searchTemplate($template)) {
			$message = new StringFormat('テンプレート "%s"が見つかりません。');
			$message[] = $template;
			throw new ViewException($message);
		}

		$params['smarty_include_tpl_file'] = $file->getPath();
		return parent::_smarty_include($params);
	}

	public function trigger_error ($error_msg, $error_type = null) {
		throw new ViewException($error_msg);
	}

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
