<?php

namespace li3_github\tests\cases\extensions\adapter\data\source\http;

use lithium\data\Connections;
use lithium\data\model\Query;
use li3_github\models\Issues;
use li3_github\models\Repos;
use li3_github\models\Orgs;
use li3_github\models\Users;

class GithubTest extends \lithium\test\Unit {

	protected $_models = array(
		'issues' => 'li3_github\models\Issues',
		'repos' => 'li3_github\models\Repos'
	);

	public function setUp() {
		Connections::add('test-gh', array(
			'type' => 'Http',
			'adapter' => 'Github',
			'login' => '',
			'password' => '',
			'socket' => 'li3_github\tests\mocks\MockGithubSocket'
		));
		Issues::config(array('connection' => 'test-gh'));
		Repos::config(array('connection' => 'test-gh'));
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

	public function testRepoIssuesCreate() {
		$data = array(
			'title' => 'New Bug',
			'body' => 'this is a new bug',
		);
		$result = Repos::create($data, array(
			'type' => 'issues',
			'user' => 'apiheadbanger',
			'repo' => 'demo',
		));
		$this->assertTrue($result->save());
	}

	public function testRepoIssuesWithSortCreatedAsc() {
		$issues = Repos::issues(array(
			'conditions' => array(
				'user' => 'octocat', 'repo' => 'Hello-World',
				'sort' => 'created', 'direction' => 'asc'
			)
		));
		$expected = '1347';
		$result = $issues->first();
		$this->assertEqual($expected, $result->number);
	}

	public function testRepoIssuesWithSortCreatedDesc() {
		$issues = Repos::issues(array(
			'conditions' => array(
				'user' => 'octocat', 'repo' => 'Hello-World',
				'sort' => 'created', 'direction' => 'desc'
			)
		));
		$result = $issues->first();
		$this->assertTrue($result->number > 2);
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
				'org' => 'octocat'
			)
		));
		$result = $repos->first();
		$this->assertEqual($result->name, 'Hello-World');
	}
	
	public function testUserOrgs() {
		$orgs = Users::orgs(array(
			'conditions' => array(
				'user' => 'octocat'
			)
		));
		
		$result = $orgs->first();
		$this->assertEqual($orgs->login, 'github');
		$this->assertEqual($orgs->id, 1);
		$this->assertEqual($orgs->url, 'https://api.github.com/orgs/1');
	}
}