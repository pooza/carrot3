<?php
namespace Carrot3;

abstract class Record implements \ArrayAccess, Assignable, AttachmentContainer, ImageContainer, HTTPRedirector {
	use HTTPRedirectorObject, BasicObject;
	protected $attributes;
	protected $table;
	protected $url;
	protected $records;
	const ACCESSOR = 'i';

	public function __construct (TableHandler $table, iterable $attributes = null) {
		$this->table = $table;
		$this->attributes = Tuple::create();
		$this->records = Tuple::create();
		if ($attributes) {
			$this->initialize($attributes);
		}
	}

	public function __call ($method, $values) {
		if (mb_ereg('^get([[:upper:]][[:alnum:]]+)$', $method, $matches)) {
			$name = $matches[1];
			if (!$this->records->hasParameter($name)) {
				$table = TableHandler::create($name);
				$this->records[$name] = $table->getRecord($this[$table->getName() . '_id']);
			}
			return $this->records[$name];
		}

		$message = new StringFormat('仮想メソッド"%s"は未定義です。');
		$message[] = $method;
		throw new \BadFunctionCallException($message);
	}

	public function initialize (iterable $attributes) {
		$this->attributes->clear();
		$this->attributes->setParameters($attributes);
		return true;
	}

	public function getAttribute (string $name) {
		return $this->attributes[StringUtils::toLower($name)];
	}

	public function getAttributes ():Tuple {
		return clone $this->attributes;
	}

	protected function createCriteria ():Criteria {
		$criteria = $this->getDatabase()->createCriteria();
		$criteria->register($this->getTable()->getKeyField(), $this);
		return $criteria;
	}

	public function update ($values, int $flags = 0) {
		if (!$this->isUpdatable()) {
			throw new DatabaseException($this . 'を更新することはできません。');
		}

		$values = Tuple::create($values);
		$db = $this->getDatabase();
		$table = $this->getTable();
		$fields = $table->getProfile()->getFields();
		$key = $table->getUpdateDateField();
		if (!$values->hasParameter($key) && $fields[$key]) {
			$values[$key] = Date::create()->format('Y-m-d H:i:s');
		}
		if (!$values->count()) {
			return;
		}

		$db->exec(SQL::getUpdateQuery($table, $values, $this->createCriteria(), $db));
		$this->attributes->setParameters($values);
		if (($record = $this->getParent()) && !($flags & Database::WITHOUT_PARENT)) {
			$record->touch();
		}
		if (($this instanceof Serializable) && !($flags & Database::WITHOUT_SERIALIZE)) {
			$this->removeSerialized();
		}
		if (!($flags & Database::WITHOUT_LOGGING)) {
			$this->getDatabase()->log($this . 'を更新しました。');
		}
	}

	protected function isUpdatable ():bool {
		return false;
	}

	public function touch () {
		$this->update([], Database::WITHOUT_LOGGING);
	}

	public function delete (int $flags = 0) {
		if (!$this->isDeletable()) {
			throw new DatabaseException($this . 'を削除することはできません。');
		}

		if (!$this->getDatabase()->hasForeignKey()) {
			foreach ($this->getTable()->getChildClasses() as $class) {
				$table = TableHandler::create($class);
				$table->getCriteria()->register($this->getTable()->getName() . '_id', $this);
				foreach ($table as $record) {
					$record->delete();
				}
			}
		}
		if (($record = $this->getParent()) && !($flags & Database::WITHOUT_PARENT)) {
			$record->touch();
		}
		$this->getDatabase()->exec(
			SQL::getDeleteQuery($this->getTable(), $this->createCriteria())
		);
		foreach ($this->getTable()->getImageNames() as $field) {
			$this->removeImageFile($field);
		}
		foreach ($this->getTable()->getAttachmentNames() as $field) {
			$this->removeAttachment($field);
		}
		if ($this instanceof Serializable) {
			$this->removeSerialized();
		}
		if (!($flags & Database::WITHOUT_LOGGING)) {
			$this->getDatabase()->log($this . 'を削除しました。');
		}
	}

	protected function isDeletable ():bool {
		return false;
	}

	public function isVisible ():bool {
		return ($this['status'] == 'show');
	}

	public function getTable () {
		return $this->table;
	}

	public function getDatabase ():Database {
		return $this->getTable()->getDatabase();
	}

	public function getParent () {
		return null;
	}

	public function getID () {
		return $this[$this->getTable()->getKeyField()];
	}

	public function getUpdateDate ():?Date {
		return Date::create($this[$this->getTable()->getUpdateDateField()]);
	}

	public function getCreateDate ():?Date {
		return Date::create($this[$this->getTable()->getCreateDateField()]);
	}

	public function sendMail ($template, iterable $params = []) {
		try {
			$mail = new SmartyMail;
			$mail->getRenderer()->setTemplate(
				Utils::getShortClass($this) . '.' . $template . '.mail'
			);
			$mail->getRenderer()->setAttribute(
				StringUtils::underscorize(Utils::getShortClass($this)),
				$this
			);
			$mail->getRenderer()->setAttribute('params', $params);
			$mail->send();
			unset($mail);
		} catch (\Throwable $e) {
			throw new MailException('メールの送信に失敗しました。', $e->getCode(), $e);
		}
	}

	public function getAttachment (string $name):?File {
		$finder = new MediaFileFinder;
		$finder->clearDirectories();
		$finder->registerDirectory($this->getTable()->getDirectory());
		$finder->registerSuffixes(MIMEType::getInstance()->getAllSuffixes());
		return $finder->execute($this->getAttachmentBaseName($name));
	}

	public function setAttachment (string $name, File $file, ?string $filename = null) {
		if ($file instanceof ImageFile) {
			$this->removeImageFile($name);
			$file->rename($this->getAttachmentBaseName($name));
		} else {
			$this->removeAttachment($name);
			if (StringUtils::isBlank($suffix = $file->getSuffix())) {
				if (StringUtils::isBlank($filename)) {
					$file->setBinary(true);
					$suffix = MIMEType::getSuffix($file->analyzeType());
				} else {
					$suffix = FileUtils::getSuffix($filename);
				}
			}
			$file->rename($this->getAttachmentBaseName($name) . $suffix);
		}
		$file->moveTo($this->getTable()->getDirectory());

		$message = new StringFormat('%sの%sを設定しました。');
		$message[] = $this;
		$message[] = $this->translator->translate($name);
		$this->getDatabase()->log($message);
	}

	public function removeAttachment (string $name) {
		if ($file = $this->getAttachment($name)) {
			$file->delete();
			$message = new StringFormat('%sの%sを削除しました。');
			$message[] = $this;
			$message[] = $this->translator->translate($name);
			$this->getDatabase()->log($message);
		}
	}

	protected function getAttachmentBaseName (string $name) {
		return sprintf('%010d_%s', $this->getID(), $name);
	}

	public function getAttachmentFileName (string $name):?string {
		if ($file = $this->getAttachment($name)) {
			return $this->getAttachmentBaseName($name) . $file->getSuffix();
		}
		return null;
	}

	public function setAttachments (WebRequest $request) {
		$dir = $request->getSession()->getDirectory();
		foreach ($this->getTable()->getImageNames() as $name) {
			if ($info = $request[$name]) {
				$this->setImageFile($name, new ImageFile($info['tmp_name']));
			} else {
				foreach (Image::getSuffixes() as $suffix) {
					if ($file = $dir->getEntry($name . $suffix, 'ImageFile')) {
						$this->setImageFile($name, $file);
						break;
					}
				}
			}
		}
		foreach ($this->getTable()->getAttachmentNames() as $name) {
			if ($info = $request[$name]) {
				$this->setAttachment($name, new File($info['tmp_name']), $info['name']);
			}
		}
	}

	public function removeImageCache (string $size) {
		(new ImageManager)->removeEntry($this, $size);
	}

	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		return (new ImageManager)->getInfo($this, $size, $pixel, $flags);
	}

	public function getImageFile (string $size):?ImageFile {
		foreach (Image::getSuffixes() as $suffix) {
			$name = $this->getAttachmentBaseName($size) . $suffix;
			if ($file = $this->getTable()->getDirectory()->getEntry($name, 'ImageFile')) {
				return $file;
			}
		}
		return null;
	}

	public function setImageFile (string $size, ImageFile $file) {
		$this->setAttachment($size, $file);
	}

	public function removeImageFile (string $size) {
		$this->removeImageCache($size);
		$this->removeAttachment($size);
	}

	public function getLabel (?string $lang = 'ja'):?string {
		foreach (['name', 'label', 'title'] as $name) {
			foreach ([null, $this->getTable()->getName() . '_'] as $prefix) {
				foreach ([null, '_' . $lang] as $suffix) {
					if (!StringUtils::isBlank($label = $this[$prefix . $name . $suffix])) {
						return $label;
					}
				}
			}
		}
		return null;
	}

	final public function getName (?string $lang = 'ja'):?string {
		return $this->getLabel($lang);
	}

	public function offsetExists ($key) {
		return $this->attributes->hasParameter($key);
	}

	public function offsetGet ($key) {
		return $this->getAttribute($key);
	}

	public function offsetSet ($key, $value) {
		throw new DatabaseException('レコードの属性を直接更新することはできません。');
	}

	public function offsetUnset ($key) {
		throw new DatabaseException('レコードの属性は削除できません。');
	}

	public function getURL ():?HTTPURL {
		if (!$this->url) {
			if (StringUtils::isBlank($this['url'])) {
				$this->url = URL::create(null, 'carrot');
				$this->url['module'] = 'User' . StringUtils::pascalize(
					$this->getTable()->getName()
				);
				$this->url['action'] = 'Detail';
				$this->url['record'] = $this;
			} else {
				$this->url = URL::create($this['url']);
			}
		}
		return $this->url;
	}

	public function assign () {
		return $this->getAttributes();
	}

	public function __toString () {
		try {
			$word = $this->translator->translate($this->getTable()->getName());
		} catch (TranslateException $e) {
			$word = $this->getTable()->getName();
		}
		return sprintf('%s(%s)', $word, $this->getID());
	}
}
