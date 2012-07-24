<?php

namespace li3_github\tests\mocks;
use li3_github\tests\mocks\MockGitHubSocket\Exception;

class MockGitHubSocket extends \lithium\net\Socket {

	protected $_data = null;

	public function open(array $options = array()) {
		parent::open($options);
		return true;
	}

	public function close() {
		return true;
	}

	public function eof() {
		return true;
	}

	public function read() {
		return join("\r\n", array(
			'HTTP/1.1 200 OK',
			'Header: Value',
			'Connection: close',
			'Content-Type: text/html;charset=UTF-8',
			'',
			$this->_data
		));

		// Status: 200 OK
		// 	Link: <https://api.github.com/resource?page=2>; rel="next",
		// 	      <https://api.github.com/resource?page=5>; rel="last"
		// 	X-RateLimit-Limit: 5000
		// 	X-RateLimit-Remaining: 4999
	}

	public function write($data) {
		$url = $data->to('url');
		return $this->_data = $this->_response($url);
	}

	public function timeout($time) {
		return true;
	}

	public function encoding($charset) {
		return true;
	}

	private function _response($url) {
		if (strpos($url, '/users/octocat')) {
			$json = '/responses/users/octocat.json';
		}
		if (strpos($url, '/users/octocat/repos')) {
			$json = '/responses/users/repos.json';
		}
		if (strpos($url, '/orgs/github/repos')) {
		    $json = '/responses/orgs/repos.json';
		}
		if (strpos($url, '/users/octocat/orgs')) {
			$json = '/responses/users/orgs.json';
		}
		if (strpos($url, '/issues')) {
			$json = '/responses/issues.json';
		}
		if (strpos($url, '/repos/octocat/github/issues')) {
		    $json = '/responses/repos/issues.json';
		}
		
		if (!isset($json)) {
		    throw new Exception("Unhandled URL: " .$url);
		}
		
		return file_get_contents(__DIR__ . $json);
	}
}

?>