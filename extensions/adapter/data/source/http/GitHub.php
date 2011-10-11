<?php

namespace li3_github\extensions\adapter\data\source\http;

use \lithium\util\String;

/**
 * Lithium GitHub Data Source.
 *
 * @package li3_github\extensions\adapter\data\source\http
 * @author John Anderson
 */
class GitHub extends \lithium\data\source\Http {

	protected $_strings = array(
		'users' => '/users/{:user}/{:type}',
		'issues' => '/issues',
		'repos' => '/repos/{:user}/{:repo}/{:type}/{:id}',
		'orgs' => '/orgs/{:org}/{:type}'
	);

	protected $_params = array(
		'user', 'repo', 'type', 'id', 'org'
	);

	/**
	 * Class dependencies.
	 */
	protected $_classes = array(
		'service' => 'lithium\net\http\Service',
		'entity' => 'lithium\data\entity\Document',
		'set' => 'lithium\data\collection\DocumentSet',
	);

	/**
	 * Constructor.
	 *
	 * @param array $config Configuration options.
	 */
	public function __construct(array $config = array()) {
		if(!empty($config['token'])) {
			$login = $config['login'] . '/token';
			$password = $config['token'];
		} else {
			$login = $config['login'];
			$password = $config['password'];
		}
		$defaults = array(
			'adapter'  => 'GitHub',
			'token'    => null,
			'scheme'   => 'https',
			'auth'     => 'Basic',
			'version'  => '1.1',
			'host'     => 'api.github.com',
			'port'     => 443,
			'path'     => '',
		);
		parent::__construct(compact('login', 'password') + $config + $defaults);
	}

	/**
	 * Data source READ operation.
	 *
	 * @param string $query
	 * @param array $options
	 * @return mixed
	 */
	public function read($query, array $options = array()) {
		extract($query->export($this));
		$path = $this->_path($source, $conditions);
		foreach ($this->_params as $param) {
			unset($conditions[$param]);
		}
		$result = $this->connection->get($path, $conditions);
		$data = json_decode($result, true);

		if (empty($data)) {
			return null;
		}
		return $this->item($query->model(), $data, array('class' => 'set'));
	}

	/**
	 * Data Source CREATE operation.
	 *
	 * @param string $query
	 * @param array $options
	 * @return mixed
	 */
	public function create($query, array $options = array()) {
		extract($query->export($this));
		$conditions = $query->entity()->_config;
		$path = $this->_path($source, $conditions);
		$options = array(
			'headers' => array('Content-Type' => 'application/json'),
			'follow_location' => false,
			'type' => 'json'
		);
		$result = $this->connection->post($path, json_encode($data['data']), $options);
		$data = json_decode($result);
		return isset($data);
	}

	/**
	 * Used for object formatting.
	 *
	 * @param string $entity
	 * @param array $data
	 * @param array $options
	 * @return mixed
	 */
	public function cast($entity, array $data, array $options = array()) {
		foreach($data as $key => $val) {
			if (!is_array($val)) {
				continue;
			}
			$class = 'entity';
			$model = $entity->model();
			$data[$key] = $this->item($model, $val, compact('class'));
		}
		return parent::cast($entity, $data, $options);
	}

	/**
	 * Convert conditions to a path
	 *
	 * @param string $source
	 * @param array $conditions
	 * @return string
	 */
	protected function _path($source, array $conditions = array()) {
		if (!isset($this->_strings[$source])) {
			return null;
		}
		$string = $this->_strings[$source];
		$conditions = array_map(function($value) {
			return is_string($value) ? urlencode($value) : null;
		}, $conditions);
		$path = String::insert($string, $conditions, array('clean' => true));
		$path = rtrim(str_replace('//', '', $path), '/');
		return $path;
	}
}