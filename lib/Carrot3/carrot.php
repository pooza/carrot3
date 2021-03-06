<?php
namespace Carrot3;

function p ($var) {
	if (!headers_sent()) {
		header('Content-Type: text/html; charset=utf-8');
	}
	if ($var instanceof Tuple) {
		$var = $var->decode();
	}
	var_dump($var);
}

function l ($var) {
	LogManager::getInstance()->put($var, 'debug');
}

function d ($var) {
	if (!($var instanceof ExceptionAlarter)) {
		$var = new StringFormat((string)$var);
	}
	(new DiscordWebhookService)->say($var);
}

spl_autoload_register(function ($name) {
	require_once BS_LIB_DIR . '/Carrot3/loader/Loader.class.php';
	Loader::getInstance()->includeClass($name);
});

set_error_handler(function ($severity, $message, $file, $line) {
	if (error_reporting() & $severity) {
		$message = sprintf(
			'%s (file:%s line:%d)',
			$message,
			str_replace(BS_ROOT_DIR . '/', '', $file),
			$line
		);
		throw new \ErrorException($message, 0, $severity, $file, $line);
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

if (PHP_SAPI != 'cli') {
	set_time_limit(BS_APP_TIME_LIMIT);
}
date_default_timezone_set(BS_DATE_TIMEZONE);
ini_set('memory_limit', BS_APP_MEMORY_LIMIT);
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
	ini_set('display_errors', 0);
	try {
		Controller::getInstance()->dispatch();
	} catch (Exception $e) {
		print 'エラーが発生しました。しばらくお待ち下さい。';
	} catch (\Throwable $e) {
		throw new Exception($e->getMessage());
	}
}
