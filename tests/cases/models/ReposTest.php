<?php

namespace li3_github\tests\cases\models;

use lithium\data\Connections;
use lithium\data\model\Query;
use li3_github\models\Issues;
use li3_github\models\Orgs;
use li3_github\models\Users;
use li3_github\models\Repos;

class ReposTest extends \lithium\test\Unit {

    public function setUp() {
	Connections::add('test-gh', array(
	    'type' => 'http',
	    'adapter' => 'GitHub',
	    'login' => '',
	    'password' => '',
	    'socket' => 'li3_github\tests\mocks\MockGitHubSocket'
	));
	Repos::config(array('connection' => 'test-gh'));
    }

    public function tearDown() {
	
    }

    public function testReposIssues() {
	$repos = Repos::issues(array('conditions' => array(
			'repo' => 'github',
			'user' => 'octocat'
			)));

	$result = $repos->first();

	$this->assertEqual($result->title, "Found a bug");
	$this->assertEqual($result->state, "open");
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

}
?>