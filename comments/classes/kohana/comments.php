<?php
class Kohana_Comments {
	static function factory (ORM $model) {
		return new static($model);
	}

	static function render (ORM $model) {
		echo static::factory($model)->comments_view();
	}

	static function form (ORM $model, $id = null) {
		echo static::factory($model)->form_view($id);
	}

	static function get (ORM $model) {
		return static::factory($model)->get_comments();
	}

	/**
	 * @var ORM
	 */
	protected $model;
	/**
	 * @var Auth
	 */
	protected $auth;

	public function __construct(ORM $model) {
		$this->auth  = Auth::instance();
		$this->model = array (
			'id'   => $model->id,
			'name' => $model->object_name()
		);
	}

	public function get_comments () {
		return ORM::factory('comment')
			->where('model_id',   '=', $this->model['id'])
			->where('model_name', '=', $this->model['name']);
	}

	public function comments_view () {
		$view = new View('comments/comments');
		$view->comments = $this->get_comments()->find_all();
		return $view;
	}

	public function form_view ($id = null) {
		if (!$this->can_comment()) return $this->cant_comment_view();

		$view = new View('comments/form');

		$comment = $this->find_comment($id);
		$errors  = $this->try_save_comment($comment);

		$view->comment = $comment;
		$view->show_username_field = $this->can_set_username();
		$view->errors = $this->render_errors($errors);
		
		return $view;
	}

	protected function render_errors ($errors) {
		if (!$errors) return '';
		
		$view = new View('comments/error');
		$view->message = __(Kohana::message('comments', 'validation_failed'));
		$view->errors  = $errors;
		return (string) $view;
	}

	protected function try_save_comment (Model_Comment $comment) {
		$form = $this->get_post();

		if ($form['content'] === null || !$this->can_comment()) return null;

		if ($comment->values($form)->check()) {
			$this->set_modified($comment)->save();
			$this->redirect($comment);
		}
		return $comment->validate()->errors('comment');
	}

	protected function set_modified (Model_Comment $comment) {
		if ($comment->id) {
			$comment->modified = DB::expr('NOW()');
		}
		return $comment;
	}

	protected function redirect (Model_Comment $comment) {
		HTTP::redirect(Request::current()->uri());
	}

	protected function find_comment ($id) {
		if (!$id) {
			$comment = $this->create_comment();
		} else {
			$comment = ORM
				::factory('comment', $id)
				->with('author')
				->find();
			if (!$comment) {
				throw new Kohana_Exception("No comment with id «$id»");
			}
		}
		return $comment;
	}

	protected function create_comment () {
		$comment = ORM::factory('comment');
		$this->set_comment_author($comment);
		$this->set_comment_model($comment);
		return $comment;
	}

	protected function set_comment_author(Model_Comment $comment) {
		if ($this->auth->logged_in()) {
			$comment->author = $this->auth->get_user();
		}
	}

	protected function set_comment_model(Model_Comment $comment) {
		$comment->model_id = $this->model['id'];
		$comment->model_name = $this->model['name'];
	}

	protected function cant_comment_view () {
		return '';
	}

	protected function can_comment() {
		return true;
	}

	protected function can_set_username() {
		return !$this->auth->logged_in();
	}

	protected function get_post () {
		$post = Arr::extract($_POST, array('comment-username','comment-content'));
		return array(
			'username' => $post['comment-username'],
			'content'  => $post['comment-content']
		);
	}
}
