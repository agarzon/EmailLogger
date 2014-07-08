<?php
App::uses('CakeLogInterface', 'Log');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeSession', 'Model/Datasource');
App::uses('ErrorHandler', 'Error');

class EmailLog implements CakeLogInterface {

	public $config = array(
		'levels' => array('warning', 'notice', 'debug', 'info', 'error'),
		'email' => 'logger',
		'duplicates' => false,
		'file' => 'logger.log'
	);

	public function __construct($config = array()) {
		$this->config = array_merge($this->config, $config);
		$this->config['file'] = LOGS . $this->config['file'];
	}

	public function write($type, $message) {
		extract($this->config);
		if (empty($levels) || in_array($type, $levels)) {
			if ($duplicates || (!$duplicates && strpos(file_get_contents($file), $message) === false)) {
				try {
					$subject = __d('logger', 'Error notification from %s type: %s', env('HTTP_HOST'), $type);
					$session = __d('logger', 'Session: %s', print_r(CakeSession::read(), true));
					$request = __d('logger', 'Request: %s', print_r($_REQUEST, true));
					$server = __d('logger', 'Server: %s', print_r($_SERVER, true));
					$content = "
						$message

						$session

						$request

						$server
					";
					CakeEmail::deliver(null, $subject, $content, $email);
					if (!$duplicates) {
						$output = $message . "\n";
						file_put_contents($file, $output, FILE_APPEND);
					}
				} catch(Exception $e) {
					// Nothing
				}
			}
		}
	}
}