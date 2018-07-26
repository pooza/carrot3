<?php
namespace Carrot3;

class Module implements HTTPRedirector, Assignable {
	use HTTPRedirectorObject, BasicObject, KeyGenerator;
	protected $name;
	protected $title;
	protected $url;
	protected $directories;
	protected $actions;
	protected $config = [];
	protected $configFiles;
	protected $prefix;
	protected $record;
	protected $table;
	protected $params;
	protected $recordClass;
	static private $instances;
	static private $prefixes = [];
	const ACCESSOR = 'm';

	protected function __construct (string $name) {
		$this->name = $name;
		$this->actions = Tuple::create();

		if (!$this->getDirectory()) {
			throw new Exception($this . 'のディレクトリが見つかりません。');
		}
		if ($file = $this->getConfigFile('module')) {
			$this->config = Tuple::create(ConfigManager::getInstance()->compile($file));
		}
		if ($file = $this->getConfigFile('filters')) {
			$this->config['filters'] = Tuple::create($file->getResult());
		}
	}

	static public function getInstance (string $name) {
		if (!self::$instances) {
			self::$instances = Tuple::create();
		}
		if (!self::$instances[$name]) {
			try {
				if ($class = Loader::getInstance()->getClass($name . 'module')) {
					$module = new $class($name);
				}
			} catch (LOaderException $e) {
				$module = new self($name);
			}
			self::$instances[$name] = $module;
		}
		return self::$instances[$name];
	}

	public function getAttributes ():Tuple {
		return Tuple::create([
			'name' => $this->getName(),
			'title' => $this->getTitle(),
			'title_menu' => $this->getMenuTitle(),
			'record_class' => $this->getRecordClass(),
		]);
	}

	public function getName ():string {
		return $this->name;
	}

	public function getTitle () {
		if (StringUtils::isBlank($this->title)) {
			if (StringUtils::isBlank($title = $this->getConfig('title'))) {
				if (StringUtils::isBlank($title = $this->getRecordClass('ja'))) {
					$title = $this->getName();
				} else if ($this->isAdminModule()) {
					$title .= '管理';
				}
			}
			$this->title = mb_ereg_replace('モジュール$', '', $title) . 'モジュール';
		}
		return $this->title;
	}

	public function getMenuTitle ():string {
		if (StringUtils::isBlank($title = $this->getConfig('title_menu'))) {
			if (StringUtils::isBlank($title = $this->getConfig('title'))) {
				if (StringUtils::isBlank($title = $this->getRecordClass('ja'))) {
					$title = $this->getName();
				}
			}
		}
		return $title;
	}

	public function getDirectory (string $name = 'module'):?Directory {
		if (!$this->directories) {
			$this->directories = Tuple::create();
		}
		if (!$this->directories[$name]) {
			switch ($name) {
				case 'module':
					$dir = FileUtils::getDirectory('modules');
					$this->directories['module'] = $dir->getEntry($this->getName());
					break;
				default:
					$this->directories[$name] = $this->getDirectory('module')->getEntry($name);
					break;
			}
		}
		return $this->directories[$name];
	}

	public function getParameterCache () {
		if (!$this->params) {
			$this->params = Tuple::create();
			$key = $this->createKey([$this->getName(), 'params']);
			if ($params = $this->user->getAttribute($key)) {
				$this->params->setParameters($params);
			}
		}
		return $this->params;
	}

	public function cacheParameters (iterable $params) {
		$this->params = Tuple::create($params);
		$this->params->removeParameter(Module::ACCESSOR);
		$this->params->removeParameter(Action::ACCESSOR);
		$this->user->setAttribute(
			$this->createKey([$this->getName(), 'params']),
			$this->params
		);
	}

	public function clearParameterCache () {
		$this->user->removeAttribute(
			$this->createKey([$this->getName(), 'params'])
		);
	}

	public function getTable () {
		if (!$this->table && !StringUtils::isBlank($class = $this->getRecordClass())) {
			$this->table = TableHandler::create($class);
		}
		return $this->table;
	}

	public function getRecord ():?Record {
		if (!$this->record && $this->getRecordID()) {
			$this->record = $this->getTable()->getRecord($this->getRecordID());
		}
		return $this->record;
	}

	public function getRecordID () {
		return $this->user->getAttribute(
			$this->createKey([$this->getName(), 'record'])
		);
	}

	public function setRecordID ($id) {
		if ($id instanceof Record) {
			$id = $id->getID();
		} else if (is_iterable($id)) {
			$id = Tuple::create($id);
			$id = $id[$this->getTable()->getKeyField()];
		} else if (!is_numeric($id)) {
			$key = [$this->getTable()->getUniqueKeyField() => $id];
			if ($record = $this->getTable()->getRecord($key)) {
				$id = $record->getID();
			} else {
				$id = null;
			}
		}
		$this->user->setAttribute($this->createKey([$this->getName(), 'record']), $id);
		$this->record = null;
	}

	public function clearRecordID () {
		$this->user->removeAttribute(
			$this->createKey([$this->getName(), 'record'])
		);
		$this->record = null;
	}

	public function getConfigFile (string $name = 'module'):?ConfigFile {
		if (!$this->configFiles) {
			$this->configFiles = Tuple::create();
		}
		if (!$this->configFiles[$name]) {
			$finder = new FileFinder('ConfigFile');
			$finder->clearDirectories();
			$finder->registerDirectory($this->getDirectory());
			if ($dir = $this->getDirectory('config')) {
				$finder->registerDirectory($dir);
			}
			$finder->registerSuffix('yaml');
			$this->configFiles[$name] = $finder->execute($name);
		}
		return $this->configFiles[$name];
	}

	public function getConfig ($key, $section = 'module') {
		if (isset($this->config[$section][$key])) {
			return $this->config[$section][$key];
		}
	}

	public function getValidationFile (string $name):?ConfigFile {
		if ($dir = $this->getDirectory('validate')) {
			return ConfigManager::getConfigFile($dir->getPath() . '/' . $name);
		}
		return null;
	}

	public function getAction (string $name):Action {
		if (!$this->actions[$name]) {
			$class = $this->loader->getClass($this->getNamespace() . '\\' . $name . 'Action');
			$this->actions[$name] = new $class($this);
		}
		return $this->actions[$name];
	}

	public function getCredential ():?string {
		if ($file = $this->getConfigFile('filters')) {
			foreach ($file->getResult() as $section) {
				if ($section['class'] == 'SecurityFilter') {
					if (!StringUtils::isBlank($section['params']['credential'])) {
						return $section['params']['credential'];
					}
				}
			}
		}
		return $this->getPrefix();
	}

	public function getNamespace ():string {
		return __NAMESPACE__ . '\\' . $this->getName() . 'Module';
	}

	public function getPrefix ():?string {
		if (!$this->prefix) {
			$pattern = '^(' . self::getPrefixes()->join('|') . ')';
			if (mb_ereg($pattern, $this->getName(), $matches)) {
				$this->prefix = $matches[1];
			}
		}
		return $this->prefix;
	}

	public function isAdminModule ():bool {
		return $this->getPrefix() == 'Admin';
	}

	public function getURL ():?HTTPURL {
		if (!$this->url) {
			$this->url = URL::create(null, 'carrot');
			$this->url['module'] = $this;
		}
		return $this->url;
	}

	public function getRecordClass (?string $lang = null):?string {
		if (!$this->recordClass) {
			if (StringUtils::isBlank($name = $this->getConfig('record_class'))) {
				$pattern = '^' . $this->getPrefix() . '([[:upper:]][[:alpha:]]+)$';
				if (mb_ereg($pattern, $this->getName(), $matches)) {
					$name = $matches[1];
				}
			}
			if (!StringUtils::isBlank($name)) {
				try {
					$this->recordClass = $this->loader->getClass($name);
				} catch (\Throwable $e) {
					return null;
				}
			}
		}
		if (!StringUtils::isBlank($this->recordClass)) {
			if (StringUtils::isBlank($lang)) {
				return $this->loader->getClass($this->recordClass);
			} else {
				return $this->translator->translate(
					StringUtils::underscorize($this->recordClass)
				);
			}
		}
		return null;
	}

	public function assign () {
		return $this->getAttributes();
	}

	public function __toString () {
		return sprintf('モジュール "%s"', $this->getName());
	}

	static public function getPrefixes () {
		if (!self::$prefixes) {
			self::$prefixes = Tuple::create(BS_MODULE_PREFIXES);
		}
		return self::$prefixes;
	}
}
