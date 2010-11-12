<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Categories extends Controller_Abstract_Overall {

	public function action_index () {
		$view = new View('categories');
		$view->cats = ORM::factory('category')->load_tree();

		$this
			->set_title(__('Categories list'))
			->set_content($view);
	}

	public function action_add () {
		$cat = ORM::factory('category');
		$cat->title = Arr::get($_REQUEST, 'title');
		$cat->insert();
		$this->json_cat($cat);
	}

	public function action_delete () {
		if ($this->user && $this->user->is_admin()) {
			ORM::factory('category',
					(int) Arr::get($_REQUEST, 'id')
				)
				->delete_branch();
			$this->json(array('deleted' => 1));
		} else {
			$this->json(array('deleted' => 0));
		}
	}

	public function action_move () {
		$form = Arr::extract($_REQUEST, array('parent_id', 'cat_id', 'position'));
		$par  = (int) $form['parent_id'];
		$cat  = (int) $form['cat_id'];
		$pos  = (int) $form['position'];

		$cat = ORM::factory('category', $cat);

		$parentChanged = $cat->parent->id != $par;

		if (!$parentChanged && $cat->position != $pos) {
			$this->json();
		}

		if ($parentChanged) {
			$cat->move($par);
		}
		$cat->set_position($pos);

		$this->json();
	}

	protected function json_cat(Model_Category $cat) {
		$this->json($cat->as_array());
	}

	protected function json (array $data = array()) {
		exit(json_encode($data));
	}
}