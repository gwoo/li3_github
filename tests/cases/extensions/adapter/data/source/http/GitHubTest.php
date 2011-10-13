<?php

namespace li3_github\tests\cases\extensions\adapter\data\source\http;

use lithium\data\Connections;
use lithium\data\model\Query;
use li3_github\models\Issues;
use li3_github\models\Repos;
use li3_github\models\Orgs;
use li3_github\models\Users;

class GitHubTest extends \lithium\test\Unit {

	protected $_models = array(
		'issues' => 'li3_github\models\Issues',
		'repos' => 'li3_github\models\Repos'
	);

	public function setUp() {
		Connections::add('test-gh', array(
			'type' => 'http',
			'adapter' => 'GitHub',
			'login' => '',
			'password' => '',
			'socket' => 'li3_github\tests\mocks\MockGitHubSocket'
		));
		Issues::config(array('connection' => 'test-gh'));
		Repos::config(array('connection' => 'test-gh'));
		Users::config(array('connection' => 'test-gh'));
		Orgs::config(array('connection' => 'test-gh'));
	}

	public function testBasicGet() {
		$gh = Connections::get('test-gh');
		$headers = array('Content-Type' => 'application/json');
		$expected = 'User';
		$results = json_decode(
			$gh->connection->get('users/octocat', array(), compact('headers'))
		);
		$this->assertEqual($expected, $results->type);
	}

	public function testIssuesRead() {
		$gh = Connections::get('test-gh');
		$query = new Query(array('model' => $this->_models['issues']));
		$results = $gh->read($query);
		$expected = 'octocat';
		$result = $results->first();
		$this->assertEqual($expected, $result->user->login);
	}

	public function testRepoIssues() {
		$issues = Repos::issues(array(
			'conditions' => array(
				'user' => 'octocat', 'repo' => 'Hello-World'
			)
		));
		$expected = 'octocat';
		$result = $issues->first();
		$this->assertEqual($expected, $result->user->login);
	}

	public function testUsersRepos() {
		$repos = Users::repos(array(
			'conditions' => array(
				'user' => 'octocat'
			)
		));

		$result = $repos->first();
		$this->assertEqual($result->name, 'Hello-World');
	}

	public function testOrgsRepos() {
		$repos = Orgs::repos(array(
			'conditions' => array(
			    'org' => 'github'
			)
		));
		$result = $repos->first();
		$this->assertEqual($result->name, 'Github');
	}

	public function testUserOrgs() {
		$orgs = Users::orgs(array(
			'conditions' => array(
				'user' => 'octocat'
			)
		));

		$result = $orgs->first();
		$this->assertEqual($result->login, 'github');
		$this->assertEqual($result->id, 1);
		$this->assertEqual($result->url, 'https://api.github.com/orgs/1');
	}
}