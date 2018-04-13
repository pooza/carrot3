<?php
/**
 * @package jp.co.b-shock.carrot3
 * @subpackage file
 */

namespace Carrot3;

/**
 * ファイル
 *
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */
class File extends DirectoryEntry implements \ArrayAccess, Renderer, Serializable {
	use SerializableMethods;
	protected $error;
	protected $handle;
	protected $attributes;
	private $mode;
	private $lines;
	private $binary = false;
	const LINE_SEPARATOR = "\n";

	/**
	 * @access public
	 * @param string $path パス
	 */
	public function __construct ($path) {
		$this->setPath($path);
		$this->analyze();
	}

	/**
	 * @access public
	 */
	public function __destruct () {
		if ($this->isOpened()) {
			$this->close();
		}
	}

	/**
	 * ユニークなファイルIDを返す
	 *
	 * @access public
	 * @return int ID
	 */
	public function getID () {
		if (!$this->id) {
			$this->id = Crypt::digest([
				$this->getPath(),
				$this->getSize(),
				fileinode($this->getPath()),
				$this->getUpdateDate()->getTimestamp(),
			]);
		}
		return $this->id;
	}

	/**
	 * メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getType () {
		return MIMEType::getType($this->getSuffix());
	}

	/**
	 * メディアタイプのメイン部を返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function getMainType () {
		return MIMEUtils::getMainType($this->getType());
	}

	/**
	 * ファイルを解析
	 *
	 * @access protected
	 */
	protected function analyze () {
		$this->attributes['filename'] = $this->getName();
		$this->attributes['path'] = $this->getPath();
		$this->attributes['size'] = $this->getSize();
	}

	/**
	 * バイナリファイルか？
	 *
	 * @access public
	 * @return bool バイナリファイルならTrue
	 */
	public function isBinary ():bool {
		return $this->binary;
	}

	/**
	 * バイナリファイルかどうかのフラグを設定
	 *
	 * @access public
	 * @param bool $flag バイナリファイルならTrue
	 */
	public function setBinary (bool $flag) {
		return $this->binary = !!$flag;
	}

	/**
	 * ファイルの内容から、メディアタイプを返す
	 *
	 * @access public
	 * @return string メディアタイプ
	 */
	public function analyzeType () {
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

	/**
	 * 規定のサフィックスを返す
	 *
	 * @access public
	 * @return string 規定サフィックス
	 */
	public function getDefaultSuffix () {
		return MIMEType::getSuffix($this->getType());
	}

	/**
	 * リネーム
	 *
	 * @access public
	 * @param string $name 新しい名前
	 */
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

	/**
	 * 移動
	 *
	 * @access public
	 * @param Directory $dir 移動先ディレクトリ
	 */
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

	/**
	 * コピー
	 *
	 * @access public
	 * @param Directory $dir コピー先ディレクトリ
	 * @param string $class クラス名
	 * @return File コピーされたファイル
	 */
	public function copyTo (Directory $dir, $class = 'File') {
		$file = parent::copyTo($dir);
		$class = $this->loader->getClass($class);
		return new $class($file->getPath());
	}

	/**
	 * 削除
	 *
	 * @access public
	 */
	public function delete () {
		if (!unlink($this->getPath())) {
			throw new FileException($this . 'を削除できません。');
		}
	}

	/**
	 * ストリームを開く
	 *
	 * @access public
	 * @param string $mode モード
	 */
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

	/**
	 * ストリームを閉じる
	 *
	 * @access public
	 */
	public function close () {
		if ($this->isOpened()) {
			fclose($this->handle);
		}
		$this->handle = null;
		$this->mode = null;
	}

	/**
	 * ストリームに1行書き込む
	 *
	 * @access public
	 * @param string $str 書き込む内容
	 */
	public function putLine ($str = '', $separator = self::LINE_SEPARATOR) {
		if (!$this->isOpened() || !in_array($this->mode[0], ['w', 'a'])) {
			throw new FileException($this . 'はw又はaモードで開かれていません。');
		}

		flock($this->handle, LOCK_EX);
		fwrite($this->handle, $str . $separator);
		flock($this->handle, LOCK_UN);
		$this->lines = null;
	}

	/**
	 * ストリームから1行読み込む
	 *
	 * @access public
	 * @param int $length 一度に読み込む最大のサイズ
	 * @return string 読み込んだ内容
	 */
	public function getLine (int $length = 4096) {
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

	/**
	 * 全ての行を返す
	 *
	 * @access public
	 * @return Tuple 読み込んだ内容の配列
	 */
	public function getLines () {
		if (!$this->lines) {
			$this->lines = Tuple::create();
			$this->lines->merge(file($this->getPath(), FILE_IGNORE_NEW_LINES));
		}
		return $this->lines;
	}

	/**
	 * 全て返す
	 *
	 * @access public
	 * @return string 読み込んだ内容
	 */
	public function getContents ():string {
		return file_get_contents($this->getPath());
	}

	/**
	 * 書き換える
	 *
	 * @access public
	 * @param string $contents 書き込む内容
	 */
	public function setContents ($contents) {
		file_put_contents($this->getPath(), $contents, LOCK_EX);
	}

	/**
	 * ウィルスなどに感染しているか？
	 *
	 * @access public
	 * @return bool 感染していたらtrue
	 */
	public function isInfected ():bool {
		$command = new CommandLine('bin/' . BS_CLAMAV_COMMAND);
		$command->setDirectory(FileUtils::getDirectory('clamav'));
		$command->push('--no-summary');
		$command->push($this->getPath());

		if ($command->getReturnCode() == 1) {
			$pattern = '^' . $this->getPath() . ': (.*)$';
			if (mb_ereg($pattern, $command->getResult()->join("\n"), $matches)) {
				$this->error = $matches[1];
				return true;
			}
		}

		return false;
	}

	/**
	 * 開かれているか？
	 *
	 * @access public
	 * @return bool 開かれていたらtrue
	 */
	public function isOpened ():bool {
		return is_resource($this->handle);
	}

	/**
	 * ポインタがEOFに達しているか？
	 *
	 * @access public
	 * @return bool EOFに達していたらtrue
	 */
	public function isEof ():bool {
		if (!$this->isReadable()) {
			throw new FileException($this . 'を読み込めません。');
		}
		return feof($this->handle);
	}

	/**
	 * ファイルサイズを返す
	 *
	 * @access public
	 * @return int ファイルサイズ
	 */
	public function getSize () {
		if ($this->isExists()) {
			return filesize($this->getPath());
		}
	}

	/**
	 * 書式化されたファイルサイズを文字列で返す
	 *
	 * @access public
	 * @param string $suffix サフィックス、デフォルトはバイトの略で"B"
	 * @return string 書式化されたファイルサイズ
	 */
	public function getBinarySize ($suffix = 'B') {
		return Numeric::getBinarySize($this->getSize()) . $suffix;
	}

	/**
	 * アップロードされたファイルか？
	 *
	 * @access public
	 * @return bool アップロードされたファイルならTrue
	 */
	public function isUploaded ():bool {
		return is_uploaded_file($this->getPath());
	}

	/**
	 * 属性を返す
	 *
	 * @access public
	 * @param string $name 属性名
	 * @return mixed 属性
	 */
	public function getAttribute (string $name) {
		return $this->attributes[$name];
	}

	/**
	 * 全ての属性を返す
	 *
	 * @access public
	 * @return Tuple 全ての属性
	 */
	public function getAttributes () {
		return $this->attributes;
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
		throw new FileException($this . 'の属性を設定できません。');
	}

	/**
	 * @access public
	 * @param string $key 添え字
	 */
	public function offsetUnset ($key) {
		throw new FileException($this . 'の属性を削除できません。');
	}

	/**
	 * 出力可能か？
	 *
	 * @access public
	 * @return bool 出力可能ならTrue
	 */
	public function validate ():bool {
		if (!$this->isReadable()) {
			$this->error = $this . 'が開けません。';
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
	 * ダイジェストを返す
	 *
	 * @access public
	 * @return string ダイジェスト
	 */
	public function digest ():string {
		return $this->getID();
	}

	/**
	 * シリアライズ
	 *
	 * @access public
	 */
	public function serialize () {
		throw new FileException('シリアライズできません。');
	}

	/**
	 * シリアライズ時の値を返す
	 *
	 * @access public
	 * @return mixed シリアライズ時の値
	 */
	public function getSerialized () {
		if ($this->isExists()) {
			return (new SerializeHandler)->getAttribute($this, $this->getUpdateDate());
		}
	}

	/**
	 * @access public
	 * @return string 基本情報
	 */
	public function __toString () {
		return sprintf('ファイル "%s"', $this->getShortPath());
	}
}
