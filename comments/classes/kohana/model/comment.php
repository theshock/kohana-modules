<?php defined('SYSPATH') or die('No direct access allowed.');

class Kohana_Model_Comment extends ORM {
	protected $_belongs_to = array(
		'author'  => array('model' => 'user')
	);

	// Validation rules
	protected $_rules = array(
		'content' => array(
			'not_empty'  => NULL,
			'min_length' => array(4),
			'max_length' => array(65536)
		),
		'username' => array(
			'min_length' => array(4),
			'max_length' => array(32)
		)
	);

	// Field labels
	protected $_labels = array(
		'content' => 'Comment content'
	);

	public function get_author_name () {
		return $this->username ?: (
			$this->author->username ?: Kohana::message('comments', 'anonymous')
		);
	}
}