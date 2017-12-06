<?php
/**
 * carrotブートローダー
 *
 * @package jp.co.b-shock.carrot3
 * @author 小石達也 <tkoishi@b-shock.co.jp>
 */

namespace Carrot3;

/**
 * デバッグ出力
 *
 * @access public
 * @param mixed $var 出力対象
 */
function p ($var) {
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=utf-8');
	}
	if (extension_loaded('xdebug')) {
		var_dump($var);
	} else {
		print("<div align=\"left\"><pre>\n");
		print_r($var);
		print("</pre></div>\n");
	}
}

/**
 * ログ出力
 *
 * @access public
 * @param mixed $var 出力対象
 */
function putlog ($var) {
	LogManager::getInstance()->put($var, 'debug');
}

/*
 * ここから処理開始
 */

spl_autoload_register(function ($name) {
	require_once BS_LIB_DIR . '/Carrot3/loader/Loader.class.php';
	Loader::getInstance()->includeClass($name);
});

set_error_handler(function ($severity, $message, $file, $line) {
	if (error_reporting() & $severity) {
		throw new \ErrorException($message, 0, $severity, $file, $line);
	}
});

register_shutdown_function(function () {
	$error = error_get_last();
	$types = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
	if (in_array($error['type'], $types)) {
		throw new \RuntimeException(sprintf(
			'%s (file:%s line:%d)',
			$error['message'],
			str_replace(BS_ROOT_DIR . '/', '', $error['file']),
			$error['line']
		));
	}
});

define('BS_LIB_DIR', BS_ROOT_DIR . '/lib');
define('BS_SHARE_DIR', BS_ROOT_DIR . '/share');
define('BS_VAR_DIR', BS_ROOT_DIR . '/var');
define('BS_BIN_DIR', BS_ROOT_DIR . '/bin');
define('BS_WEBAPP_DIR', BS_ROOT_DIR . '/webapp');

if (PHP_SAPI == 'cli') {
	$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
	$_SERVER['HTTP_USER_AGENT'] = 'Console';
	$_SERVER['SERVER_NAME'] = basename(BS_ROOT_DIR);
}

foreach ([$_SERVER['SERVER_NAME'], 'application', 'carrot'] as $key) {
	ConfigManager::getInstance()->compile('constant/' . $key);
}

mb_internal_encoding('utf-8');
mb_regex_encoding('utf-8');
date_default_timezone_set(BS_DATE_TIMEZONE);
ini_set('realpath_cache_size', '256K');
ini_set('log_errors', 1);
ini_set('error_log', 'syslog');
ini_set('upload_tmp_dir', FileUtils::getPath('tmp'));

Request::getInstance()->createSession();

if (BS_DEBUG) {
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	Controller::getInstance()->dispatch();
} else {
	error_reporting(error_reporting() & ~E_DEPRECATED);
	ini_set('display_errors', 0);
	try {
		Controller::getInstance()->dispatch();
	} catch (Exception $e) {
		print 'エラーが発生しました。しばらくお待ち下さい。';
	} catch (\Exception $e) {
		throw new Exception($e->getMessage());
	}
}
