# Interface:
	class Kohana_ORM_MP extends ORM {
		protected $max_level = 32;

		readonly $children;
		readonly $siblings;
		readonly $descendants;
		readonly $parent_ids;
		readonly $parent_id;

		readonly $roots;
		readonly $root;
		readonly $parent;
		readonly $parents;

		function is_root();
		function has_children();
		function is_leaf();
		function is_descendant (ORM_MP|int $target);
		function is_child      (ORM_MP|int $target);
		function is_parent     (ORM_MP|int $target, $full_path = false);
		function is_sibling    (ORM_MP|int $target);

		function load_tree(int $id = null);
		function save();
		function insert (ORM_MP $target = null);
		function move   (ORM_MP $target = null);
		function set_position(int $position = null);

		function delete_branch();
		function get_child($id);

		// Details :

		/** is_parent :
		 * if full_path is sets to true - will check all parents
		 * else - only closest. it is equals to :
		 * $target->is_child($this)      if $full_path == false
		 * $target->is_descendant($this) if $full_path == true
		 */
		/** load_tree:
		 * call this if you want to work with descendants
		 */
		/** save:
		 * saves element as root
		 */
		/** insert:
		 * saves element as root, or as child of $target
		 */
		/** get_child:
		 * returns the child with some id, or null
		 */

	}

# Examples:

### All root elements:
	ORM::factory('Category')->roots;

### Get:
	$cat_15 = ORM::factory('Category', 15)->load_tree(  );
	$cat_15 = ORM::factory('Category'    )->load_tree(15);

### Root of element:
	$cat_15->root;

### Move:
	$cat_15->move(ORM::factory('Category', 21));
	$cat_15->set_position(4);

### Delete:
	$cat_15->delete_branch();

### Create root:
	ORM::factory('Category')
		->values($form)
		->save();

### Create leaf:
	ORM::factory('Category')
		->values($form)
		->insert( ORM::factory('Category', 40) );

### You can use ID as target instead of object:
	ORM::factory('Category')
		->values($form)
		->insert(40);

### Outputs :
	function r_render(Model_Category $category) {
		$result = "";
		foreach ($category->children as $child) {
			$sub_list = r_render($child);
			$result  .= "<li><b>{child->title}</b> {$sub_list}</li>";
		}
		return "<ul>{$result}</ul>";
	}
	echo r_render($category);


### $max_level
	when depth reach $max_level all elements will be added to parent of such deep element.
	E.g., when $max_level equals 32
	"Foo" is closest parent to "Bar" and "Foo" has 31 parents;
	If we try to add "Lua" to "Bar", it will be added to "Foo"
	because $max_level reached. but it is rare situation
