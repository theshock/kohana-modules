<?php

class Kohana_ORM_MP extends ORM {
	protected $max_level = 32;

	public function is_root() {
		return !$this->parent_id;
	}
	public function has_children() {
		return !!count($this->children);
	}
	public function is_leaf() {
		return !$this->has_children();
	}
	public function is_descendant($target) {
		return $this->target($target)->is_parent($this, true);
	}
	public function is_child($target) {
		return $this->target($target)->is_parent($this);
	}
	public function is_parent($target, $full_path = false) {
		return $full_path ?
			in_array($this->id,   $target->parent_ids) :
			         $this->id == $target->parent_id;
	}
	public function is_sibling($target) { 
		return $this->parent_id == $this->target($target)->parent_id;
	}

	protected $tree_loaded = false;
	public function load_tree ($id = null) {
		if ($id !== true && $this->tree_loaded) return $this;
		$this->tree_loaded = true;

		$cats = $this->create();
		if (is_callable($id)) {
			$id($cats);
		}
		if ($this->path || $id) {
			$path = $id ? "%.$id.%" : $this->path . '%';
			$cats->where('path', 'like', $path);
		}
		$cats = $cats
			->order_by('position')
			->find_all();

		$levels = array();

		foreach($cats as $cat) {
			$L = $cat->level;

			if (empty($levels[$L])) {
				$levels[$L] = array();
			}
			$levels[$L][] = $cat;
		}

		ksort($levels);
		foreach ($levels as $level) {
			foreach ($level as $desc) {
				$this->add_descendant($desc);
			}
		}
		return $this;
	}

	public function save () {
		return $this->id ? parent::save() : $this->insert();
	}

	public function insert ($target = null) {
		$target = $target ? $this->target($target) : null;
		return parent::save()->move($target, true);
	}

	public function move ($target = null, $new = false) {
		$target = $target ? $this->target($target) : null;
		$this->set_position(null);
		$children = $this->load_tree()->children;
		if ($target && $target->id) {
			if ($target->level == $this->max_level) {
				$target = $target->parent;
			}

			$this->level = $target->level + 1;
			$this->path  = $target->path . $this->id . '.';
			$this->position = count($target->load_tree()->children) + 1;
			$target->add_child($this);
		} else {
			$roots = $this->roots->find_all();
			$this->level = 0;
			$this->path  = '.' . $this->id . '.';
			$this->position = $roots ? count($roots) + ($new ? 0 : 1) : 0;
		}
		parent::save();
		$this->_children = array();
		foreach ($children as $child) {
			$child->move($this);
		}
		return $this;
	}

	public function set_position ($position = null) {
		$path = ($this->parent && $this->parent->id) ? $this->parent->path : '.' ;
		$posFrom = (int) $this->position;
		if ($position) {
			$posTo = (int) $position;
			$lower = $posTo < $posFrom;

			$q = DB::update($this->_table_name)
				->where('path', 'like', $path . '%')
				->where('level', '=', $this->level)
				->where('position', 'BETWEEN', array(
					min($posFrom, $posTo), max($posFrom, $posTo)
				))
				->set(array (
					'position' => DB::expr('position' . ($lower ? '+' : '-') . 1)
				))
				->execute();
			$this->position = $position;
			parent::save();
		} else {
			DB::update($this->_table_name)
				->where('path', 'like', $path . '%')
				->where('level', 'like', $this->level)
				->where('position', '>=', $posFrom)
				->set(array (
					'position' => DB::expr('position - 1')
				))
				->execute();
		}
		return $this;
	}

	public function delete_branch() {
		if ($this->path) {
			$this->set_position(null);
			DB::delete($this->_table_name)
				->where('path', 'like', $this->path . '%')
				->execute($this->_db);
		}
		return $this;
	}

	public function get_roots() {
		return $this->create()->where('level', '=', 0);
	}
	public function get_root() {
		return $this->create($this->parent_ids[0]);
	}
	public function get_parent() {
		return $this->create($this->parent_id);
	}
	public function get_parents() {
		return $this->create($this->parent_ids);
	}

	public function get_child ($id) {
		if (!$id) return null;
		return Arr::get($this->children, $id);
	}
	protected $_children = array();

	public function get_children() {
		return $this->_children;
	}
	public function get_siblings() {
		$all = $this->parent->children;
		unset($all[$this->id]);
		return $all;
	}
	// public function get_leaves() { return null; }
	public function get_descendants() {
		$descs = $this->children;
		foreach($descs as $elem) {
			$descs = array_merge($descs, $elem->descendants);
		}
		return $descs;
	}
	public function get_parent_ids () {
		$ids = explode('.', trim($this->path, '.'));
		array_pop($ids); // self id
		foreach ($ids as &$v) {
			$v = (int) $v;
		}
		return $ids;
	}
	public function get_parent_id () {
		$ids = $this->get_parent_ids();
		if (!$ids) {
			return null;
		}
		return array_pop($ids);
	}

	public function add_child ($elem) {
		$this->_children[$elem->id] = $elem;
		return $this;
	}

	public function rm_child ($elem) {
		unset($this->_children[$elem->id]);
		return $this;
	}

	protected function add_descendant ($elem) {
		$parent = $elem->parent_id;

		if ($parent == $this->id) {
			$this->add_child($elem);
		} else {
			$child = $this->get_child_parent_of($elem);
			$child && $child->add_descendant($elem);
		}
	}

	protected function get_child_parent_of ($elem) {
		$ids = $elem->parent_ids;
		foreach ($this->children as $child) {
			if (in_array($child->id, $ids)) return $child;
		}
		return null;
	}

	protected function create ($id = null) {
		$elem = new static;
		if (is_array($id)) {
			$elem->where('id', 'in', $id);
		} else if (is_int($id)) {
			$elem->where('id', '=', $id);
		}
		return $elem;
	}

	protected function target ($target) {
		if ( !$target instanceof $this ) {
			$target = self::factory($this->object_name(), array($this->primary_key() => $target));
		}
		return $target;
	}
	
	public function __get($column) {
		$values = array(
			'parent_id', 'parent_ids',
			'root', 'roots', 'parent', 'parents',
			'children', 'siblings', 'leaves', 'descendants'
		);
		return (in_array($column, $values)) ?
			$this->{"get_$column"}() :
			parent::__get($column);
	}
}
