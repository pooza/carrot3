<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage database.record
 */

namespace Carrot3;

/**
 * テーブルのレコード
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 * @abstract
 */
abstract class Record implements \ArrayAccess,
	Serializable, Assignable, AttachmentContainer, ImageContainer, HTTPRedirector {

	use HTTPRedirectorMethods, BasicObject, SerializableMethods;
	protected $attributes;
	protected $table;
	protected $url;
	protected $criteria;
	protected $records;
	protected $digest;
	const ACCESSOR = 'i';

	/**
	 * @access public
	 * @param TableHandler $table テーブルハンドラ
	 * @param iterable $attributes 属性の連想配列
	 */
	public function __construct (TableHandler $table, iterable $attributes = null) {
		$this->table = $table;
		$this->attributes = Tuple::create();
		$this->records = Tuple::create();
		if ($attributes) {
			$this->initialize($attributes);
		}
	}

	/**
	 * @access public
	 * @param string $method メソッド名
	 * @param mixed $values 引数
	 */
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

	/**
	 * 属性値を初期化
	 *
	 * @access public
	 * @param iterable $attributes 属性の連想配列
	 * @return Record 自分自身
	 */
	public function initialize (iterable $attributes) {
		$this->attributes->clear();
		$this->attributes->setParameters($attributes);
		return $this;
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return string 属性値
	 */
	public function getAttribute (string $name) {
		return $this->attributes[StringUtils::toLower($name)];
	}

	/**
	 * 全属性を返す
	 *
	 * @access public
	 * @return Tuple 全属性値
	 */
	public function getAttributes () {
		return clone $this->attributes;
	}

	/**
	 * 抽出条件を返す
	 *
	 * @access protected
	 * @return Criteria 抽出条件
	 */
	protected function getCriteria () {
		if (!$this->criteria) {
			$this->criteria = $this->createCriteria();
			$this->criteria->register($this->getTable()->getKeyField(), $this);
		}
		return $this->criteria;
	}

	/**
	 * 更新
	 *
	 * @access public
	 * @param mixed $values 更新する値
	 * @param int $flags フラグのビット列
	 *   Database::WITHOUT_LOGGING ログを残さない
	 *   Database::WITHOUT_SERIALIZE シリアライズしない
	 */
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

		$db->exec(SQL::getUpdateQuery($table, $values, $this->getCriteria(), $db));
		$this->attributes->setParameters($values);
		if (($record = $this->getParent()) && !($flags & Database::WITHOUT_PARENT)) {
			$record->touch();
		}
		if ($this->isSerializable() && !($flags & Database::WITHOUT_SERIALIZE)) {
			$this->removeSerialized();
		}
		if (!($flags & Database::WITHOUT_LOGGING)) {
			$this->getDatabase()->log($this . 'を更新しました。');
		}
	}

	/**
	 * 更新可能か？
	 *
	 * @access protected
	 * @return bool 更新可能ならTrue
	 */
	protected function isUpdatable () {
		return false;
	}

	/**
	 * 更新日付のみ更新
	 *
	 * @access public
	 */
	public function touch () {
		$this->update([], Database::WITHOUT_LOGGING);
	}

	/**
	 * 削除
	 *
	 * @access public
	 * @param int $flags フラグのビット列
	 *   Database::WITHOUT_LOGGING ログを残さない
	 */
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
			SQL::getDeleteQuery($this->getTable(), $this->getCriteria())
		);
		foreach ($this->getTable()->getImageNames() as $field) {
			$this->removeImageFile($field);
		}
		foreach ($this->getTable()->getAttachmentNames() as $field) {
			$this->removeAttachment($field);
		}
		$this->removeSerialized();
		if (!($flags & Database::WITHOUT_LOGGING)) {
			$this->getDatabase()->log($this . 'を削除しました。');
		}
	}

	/**
	 * 削除可能か？
	 *
	 * @access protected
	 * @return bool 削除可能ならTrue
	 */
	protected function isDeletable () {
		return false;
	}

	/**
	 * 表示して良いか？
	 *
	 * @access public
	 * @return bool 表示して良いならTrue
	 */
	public function isVisible () {
		return ($this['status'] == 'show');
	}

	/**
	 * 生成元テーブルハンドラを返す
	 *
	 * @access public
	 * @return TableHandler テーブルハンドラ
	 */
	public function getTable () {
		return $this->table;
	}

	/**
	 * データベースを返す
	 *
	 * @access public
	 * @return Database データベース
	 */
	public function getDatabase () {
		return $this->getTable()->getDatabase();
	}

	/**
	 * 親レコードを返す
	 *
	 * 適切にオーバーライドすれば、update等の動作が少し利口に。
	 *
	 * @access public
	 * @return Record 親レコード
	 */
	public function getParent () {
		return null;
	}

	/**
	 * 抽出条件を生成して返す
	 *
	 * @access protected
	 * @return Criteria 抽出条件
	 */
	protected function createCriteria () {
		return $this->getDatabase()->createCriteria();
	}

	/**
	 * IDを返す
	 *
	 * @access public
	 * @return int ID
	 */
	public function getID () {
		return $this[$this->getTable()->getKeyField()];
	}

	/**
	 * 更新日を返す
	 *
	 * @access public
	 * @return Date 更新日
	 */
	public function getUpdateDate () {
		return Date::create($this[$this->getTable()->getUpdateDateField()]);
	}

	/**
	 * 作成日を返す
	 *
	 * @access public
	 * @return Date 作成日
	 */
	public function getCreateDate () {
		return Date::create($this[$this->getTable()->getCreateDateField()]);
	}

	/**
	 * メールを送信
	 *
	 * @access public
	 * @param string $template テンプレート名
	 * @param iterable $params アサインするパラメータ
	 */
	public function sendMail ($template, iterable $params = null) {
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
		} catch (\Exception $e) {
			throw new MailException('メールの送信に失敗しました。', $e->getCode(), $e);
		}
	}

	/**
	 * 添付ファイルの情報を返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return Tuple 添付ファイルの情報
	 */
	public function getAttachmentInfo (string $name) {
		if (($file = $this->getAttachment($name)) && ($file instanceof Assibnable)) {
			return $file->assign();
		}
	}

	/**
	 * 添付ファイルを返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return File 添付ファイル
	 */
	public function getAttachment (string $name) {
		$finder = new MediaFileFinder;
		$finder->clearDirectories();
		$finder->registerDirectory($this->getTable()->getDirectory());
		$finder->registerSuffixes(MIMEType::getInstance()->getAllSuffixes());
		return $finder->execute($this->getAttachmentBaseName($name));
	}

	/**
	 * 添付ファイルを設定
	 *
	 * @access public
	 * @param string $name 名前
	 * @param File $file 添付ファイル
	 * @param string $filename ファイル名
	 */
	public function setAttachment (string $name, File $file, $filename = null) {
		if ($file instanceof ImageFile) {
			$this->removeImageFile($name);
			$file->rename($this->getImageFileBaseName($name));
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
		$message[] = TranslateManager::getInstance()->translate($name);
		$this->getDatabase()->log($message);
	}

	/**
	 * 添付ファイルを削除する
	 *
	 * @access public
	 * @param string $name 名前
	 */
	public function removeAttachment (string $name) {
		if ($file = $this->getAttachment($name)) {
			$file->delete();
			$message = new StringFormat('%sの%sを削除しました。');
			$message[] = $this;
			$message[] = TranslateManager::getInstance()->translate($name);
			$this->getDatabase()->log($message);
		}
	}

	/**
	 * 添付ファイルベース名を返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return string 添付ファイルベース名
	 */
	public function getAttachmentBaseName (string $name) {
		return sprintf('%010d_%s', $this->getID(), $name);
	}

	/**
	 * 添付ファイルのダウンロード時の名を返す
	 *
	 * @access public
	 * @param string $name 名前
	 * @return string ダウンロード時ファイル名
	 */
	public function getAttachmentFileName (string $name) {
		if ($file = $this->getAttachment($name)) {
			return $this->getAttachmentBaseName($name) . $file->getSuffix();
		}
	}

	/**
	 * 添付ファイルをまとめて設定
	 *
	 * @access public
	 * @param WebRequest $request リクエスト
	 */
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

	/**
	 * キャッシュをクリア
	 *
	 * @access public
	 * @param string $size
	 */
	public function removeImageCache (string $size) {
		(new ImageManager)->removeEntry($this, $size);
	}

	/**
	 * 画像の情報を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @param int $pixel ピクセル数
	 * @param int $flags フラグのビット列
	 * @return Tuple 画像の情報
	 */
	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		return (new ImageManager)->getInfo($this, $size, $pixel, $flags);
	}

	/**
	 * 画像ファイルを返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return ImageFile 画像ファイル
	 */
	public function getImageFile (string $size) {
		foreach (Image::getSuffixes() as $suffix) {
			$name = $this->getImageFileBaseName($size) . $suffix;
			if ($file = $this->getTable()->getDirectory()->getEntry($name, 'ImageFile')) {
				return $file;
			}
		}
	}

	/**
	 * 画像ファイルを設定
	 *
	 * @access public
	 * @param string $size 画像名
	 * @param ImageFile $file 画像ファイル
	 */
	public function setImageFile (string $size, ImageFile $file) {
		$this->setAttachment($size, $file);
	}

	/**
	 * 画像ファイルを削除する
	 *
	 * @access public
	 * @param string $size サイズ名
	 */
	public function removeImageFile (string $size) {
		$this->removeImageCache($size);
		$this->removeAttachment($size);
	}

	/**
	 * 画像ファイルベース名を返す
	 *
	 * @access public
	 * @param string $size サイズ名
	 * @return string 画像ファイルベース名
	 */
	public function getImageFileBaseName (string $size) {
		return sprintf('%010d_%s', $this->getID(), $size);
	}

	/**
	 * ラベルを返す
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string ラベル
	 */
	public function getLabel ($language = 'ja') {
		foreach (['name', 'label', 'title'] as $name) {
			foreach ([null, $this->getTable()->getName() . '_'] as $prefix) {
				foreach ([null, '_' . $language] as $suffix) {
					if (!StringUtils::isBlank($label = $this[$prefix . $name . $suffix])) {
						return $label;
					}
				}
			}
		}
	}

	/**
	 * ラベルを返す
	 *
	 * getLabelのエイリアス
	 *
	 * @access public
	 * @param string $language 言語
	 * @return string ラベル
	 * @final
	 */
	final public function getName ($language = 'ja') {
		return $this->getLabel($language);
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 * @return bool 要素が存在すればTrue
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
		throw new DatabaseException('レコードの属性を直接更新することはできません。');
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		throw new DatabaseException('レコードの属性は削除できません。');
	}

	/**
	 * リダイレクト対象
	 *
	 * @access public
	 * @return URL
	 */
	public function getURL () {
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

	/**
	 * シリアライズするか？
	 *
	 * @access public
	 * @return bool シリアライズするならTrue
	 */
	public function isSerializable () {
		return SerializeHandler::getClasses()->isContain(Utils::getShortClass($this));
	}

	/**
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest () {
		if (!$this->digest) {
			$this->digest = Crypt::digest([
				Utils::getClass($this),
				$this->getID(),
				$this->getUpdateDate()->getTimestamp(),
			]);
		}
		return $this->digest;
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		if (!$this->isSerializable()) {
			throw new DatabaseException($this . 'はシリアライズできません。');
		}
		(new SerializeHandler)->setAttribute($this, $this->getSerializableValues());
	}

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return mixed シリアライズ時の値
	 */
	public function getSerialized () {
		if ($date = $this->getUpdateDate()) {
			return (new SerializeHandler)->getAttribute($this, $date);
		} else {
			return (new SerializeHandler)[$this];
		}
	}

	/**
	 * 全てのファイル属性
	 *
	 * @access protected
	 * @return Tuple ファイル属性の配列
	 */
	protected function getSerializableValues () {
		$values = $this->getAttributes();
		if ($url = $this->getURL()) {
			$values['url'] = $url->getContents();
		}
		foreach ($this->getTable()->getImageNames() as $field) {
			if (!!$this->getImageFile($field)) {
				$values['has_' . $field] = true;
				$values[$field] = $this->getImageInfo($field);
			}
		}
		foreach ($this->getTable()->getAttachmentNames() as $field) {
			if (!!$this->getAttachment($field)) {
				$values['has_' . $field] = true;
				$values[$field] = $this->getAttachmentInfo($field);
			}
		}
		return $values;
	}

	/**
	 * アサインすべき値を返す
	 *
	 * @access public
	 * @return Tuple アサインすべき値
	 */
	public function assign () {
		$values = null;
		if ($this->isSerializable()) {
			if (StringUtils::isBlank($values = $this->getSerialized())) {
				$this->serialize();
			}
		}
		if (StringUtils::isBlank($values)) {
			$values = $this->getSerializableValues();
		}
		$values['is_visible'] = $this->isVisible();
		return $values;
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		try {
			$word = TranslateManager::getInstance()->execute($this->getTable()->getName());
		} catch (TranslateException $e) {
			$word = $this->getTable()->getName();
		}
		return sprintf('%s(%s)', $word, $this->getID());
	}
}
