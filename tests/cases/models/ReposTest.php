<?php

namespace li3_github\tests\cases\models;

use lithium\data\Connections;
use lithium\data\model\Query;
use li3_github\models\Issues;
use li3_github\models\Orgs;
use li3_github\models\Users;
use app\models\Repos;

class ReposTest extends \lithium\test\Unit {

    public function setUp() {
	Repos::config(array('connection' => 'test-gh'));
    }

    public function tearDown() {
	
    }

    public function testReposAll() {
	$repos = Repos::issues(array('conditions' => array(
		    'repo' => 'github',
		    'user' => 'octocat'
		)));
	
	$result = $repos->first();
	
	$this->assertEqual($result->title, "Found a bug");
	$this->assertEqual($result->state, "open");
    }

}

?>