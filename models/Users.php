<?php

namespace li3_github\models;

class Users extends \lithium\data\Model {
	
	protected $_meta = array('connection' => 'github');
}

Users::finder('repos', function($self, $params, $chain) {
	$params['options']['conditions']['type'] = 'repos';
	$data = $chain->next($self, $params, $chain);
	return $data;
});

Users::finder('orgs', function($self, $params, $chain) {
	$params['options']['conditions']['type'] = 'orgs';
	$data = $chain->next($self, $params, $chain);
	return $data;
});

?>