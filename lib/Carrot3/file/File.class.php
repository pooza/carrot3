<?php
namespace Carrot3;

class File extends DirectoryEntry implements \ArrayAccess, Renderer, ImageContainer {
	protected $error;
	protected $handle;
	protected $attributes;
	private $mode;
	private $lines;
	private $binary = false;
	const LINE_SEPARATOR = "\n";

	public function __construct ($path) {
		$this->attributes = Tuple::create();
		$this->setPath($path);
		$this->analyze();
	}

	public function __destruct () {
		if ($this->isOpened()) {
			$this->close();
		}
	}

	public function getID ():?string {
		if (!$this->id && $this->isExists()) {
			$this->id = Crypt::digest([
				$this->getPath(),
				$this->getSize(),
				fileinode($this->getPath()),
				$this->getUpdateDate()->getTimestamp(),
			]);
		}
		return $this->id;
	}

	public function getType ():string {
		return MIMEType::getType($this->getSuffix());
	}

	public function getMainType ():string {
		return MIMEUtils::getMainType($this->getType());
	}

	protected function analyze () {
		$this->attributes['filename'] = $this->getName();
		$this->attributes['path'] = $this->getPath();
		$this->attributes['size'] = $this->getSize();
	}

	public function isBinary ():bool {
		return $this->binary;
	}

	public function setBinary (bool $flag) {
		return $this->binary = !!$flag;
	}

	public function analyzeType ():string {
		if (!$this->isExists()) {
			return null;
		}
		if (!$this->isBinary()) {
			return $this->getType();
		}

		if (extension_loaded('fileinfo')) {
			$type = (new \finfo(FILEINFO_MIME_TYPE))->file($this->getPath());
		} else {
			$type = rtrim(exec('file -b --mime-type ' . $this->getPath()));
		}
		if ($type == MIMEType::DEFAULT_TYPE) {
			$type = MIMEType::getType($this->getSuffix());
		}
		return MIMEType::getInstance()->resolveType($type);
	}

	public function getDefaultSuffix ():?string {
		return MIMEType::getSuffix($this->getType());
	}

	public function rename (string $name) {
		if ($this->isOpened()) {
			throw new FileException($this . 'は既に開かれています。');
		}

		if ($this->isUploaded()) {
			$path = $this->getDirectory()->getPath() . '/' . basename($name);
			if (!move_uploaded_file($this->getPath(), $path)) {
				$message = new StringFormat('アップロードされた%sをリネームできません。');
				$message[] = $this;
				throw new FileException($message);
			}
			$this->setPath($path);
			$this->getDirectory()->clearEntryNames();
		} else {
			parent::rename($name);
		}
	}

	public function moveTo (Directory $dir) {
		if ($this->isOpened()) {
			throw new FileException($this . 'は既に開かれています。');
		}
		if ($this->isUploaded()) {
			$path = $dir->getPath() . '/' . $this->getName();
			if (!move_uploaded_file($this->getPath(), $path)) {
				$message = new StringFormat('アップロードされた%sを移動できません。');
				$message[] = $this;
				throw new FileException($message);
			}
			$this->setPath($path);
		} else {
			parent::moveTo($dir);
		}
	}

	public function copyTo (Directory $dir, $class = 'File'):DirectoryEntry {
		$file = parent::copyTo($dir);
		$class = $this->loader->getClass($class);
		return new $class($file->getPath());
	}

	public function delete () {
		if (!unlink($this->getPath())) {
			throw new FileException($this . 'を削除できません。');
		}
		if ($file = $this->getImageFile('image')) {
			$file->removeImageCache('image');
		}
	}

	public function open ($mode = 'r') {
		if (!in_array($mode[0], ['r', 'a', 'w'])) {
			$message = new StringFormat('モード "%s" が正しくありません。');
			$message[] = $mode;
			throw new FileException($message);
		} else if (($mode[0] == 'r') && !$this->isExists()) {
			throw new FileException($this . 'が存在しません。');
		} else if ($this->isOpened()) {
			throw new FileException($this . 'は既に開かれています。');
		}

		ini_set('auto_detect_line_endings', true);
		if (!$this->handle = fopen($this->getPath(), $mode)) {
			$this->handle = null;
			$this->mode = null;
			$message = new StringFormat('%sを%sモードで開くことができません。');
			$message[] = $this;
			$message[] = $mode;
			throw new FileException($message);
		}
		$this->mode = $mode;
	}

	public function close () {
		if ($this->isOpened()) {
			fclose($this->handle);
		}
		$this->handle = null;
		$this->mode = null;
	}

	public function putLine ($str = '', $separator = self::LINE_SEPARATOR) {
		if (!$this->isOpened() || !in_array($this->mode[0], ['w', 'a'])) {
			throw new FileException($this . 'はw又はaモードで開かれていません。');
		}
		flock($this->handle, LOCK_EX);
		fwrite($this->handle, $str . $separator);
		flock($this->handle, LOCK_UN);
		$this->lines = null;
	}

	public function getLine (int $length = 4096):string {
		if ($this->isOpened()) {
			if ($this->mode[0] != 'r') {
				throw new FileException($this . 'はrモードで開かれていません。');
			}
		} else {
			$this->open();
		}

		if ($this->isEof()) {
			return '';
		}
		return stream_get_line($this->handle, $length);
	}

	public function getLines ():Tuple {
		if (!$this->lines) {
			$this->lines = Tuple::create(file($this->getPath(), FILE_IGNORE_NEW_LINES));
		}
		return $this->lines;
	}

	public function getContents ():string {
		return file_get_contents($this->getPath());
	}

	public function setContents ($contents) {
		file_put_contents($this->getPath(), $contents, LOCK_EX);
	}

	public function isThumbnailable ():bool {
		foreach (ConfigManager::getInstance()->compile('mime') as $entry) {
			$entry = Tuple::create($entry);
			if ($entry['type'] == $this->getType()) {
				return $entry['thumbnailable'] && (
					$this->getSize() < (BS_IMAGE_THUMBNAIL_LIMIT_SIZE * 1024 * 1024)
				);
			}
		}
		return false;
	}

	public function isOpened ():bool {
		return is_resource($this->handle);
	}

	public function isEof ():bool {
		return $this->isReadable() && feof($this->handle);
	}

	public function getSize ():int {
		if ($this->isExists()) {
			return filesize($this->getPath());
		} else {
			return 0;
		}
	}

	public function getBinarySize ($suffix = 'B'):string {
		return Numeric::getBinarySize($this->getSize()) . $suffix;
	}

	public function isUploaded ():bool {
		return is_uploaded_file($this->getPath());
	}

	public function getAttribute (string $name) {
		return $this->attributes[$name];
	}

	public function getAttributes ():Tuple {
		return $this->attributes;
	}

	public function removeImageCache (string $size) {
		(new ImageManager)->removeEntry($this, $size);
	}

	public function getImageInfo (string $size, ?int $pixel = null, int $flags = 0) {
		if ($file = $this->getImageFile('image')) {
			$info = (new ImageManager)->getInfo($file, $size, $pixel, $flags);
			$info['alt'] = $this->getLabel();
			return $info;
		}
	}

	public function getImageFile (string $size):?ImageFile {
		if (!$this->isExists() || !$this->isThumbnailable()) {
			return null;
		}

		$dir = FileUtils::getDirectory('file_thumbnail');
		if (!$file = $dir->getEntry($this->getID(), 'ImageFile')) {
	 		$temp = FileUtils::createTemporaryFile(MIMEType::getSuffix($this->getType()));
			$temp->setContents($this->getContents());
			$params = new WWWFormRenderer;
			$params['width'] = BS_REPLACEMENT_THUMBNAIL_PIXELS;
			$params['height'] = BS_REPLACEMENT_THUMBNAIL_PIXELS;
			$params['background_color'] = BS_IMAGE_THUMBNAIL_BGCOLOR;
			$response = (new PiconService)->sendPOST('/resize', $params, $temp);
			$image = new Image;
			$image->setType('image/png');
			$image->setImage($response->getRenderer()->getContents());
			$temp->delete();
			$file = $dir->createEntry($this->getID(), 'ImageFile');
			$file->setContents($image->getContents());
		}
		return $file;
	}

	public function offsetExists ($key) {
		return $this->attributes->hasParameter($key);
	}

	public function offsetGet ($key) {
		return $this->getAttribute($key);
	}

	public function offsetSet ($key, $value) {
		throw new FileException($this . 'の属性を設定できません。');
	}

	public function offsetUnset ($key) {
		throw new FileException($this . 'の属性を削除できません。');
	}

	public function validate ():bool {
		if (!$this->isReadable()) {
			$this->error = $this . 'が開けません。';
			return false;
		}
		return true;
	}

	public function getError ():?string {
		return $this->error;
	}

	public function getLabel (?string $lang = 'ja'):?string {
		return $this->getBaseName();
	}

	public function __toString () {
		return sprintf('ファイル "%s"', $this->getShortPath());
	}
}
