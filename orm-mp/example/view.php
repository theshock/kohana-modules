<style>
.placeholder {
	display : block;
	width : 60%;
	height : 14px;
	background : #cfc;
	margin : 2px;
}
ul {
	margin : 0;
	padding : 10px 0 10px 2em;
	margin-left : 2em;
	border : 1px dashed #ccc;
}
span {
	margin : 4px;
}
ul ul {
	border-right : 0px;
}
a.delete,
span {
	font-size : 15px;
	cursor : pointer;
}
form {
	margin : 20px;
}
input {
	font : 15px monospace;
	color : black;
	background : white;
	border : 1px solid black;
	padding : 3px;
}
input[type=submit] {
	padding : 2px;
	background : #eee;
}
</style>


<? $rCat = function ($cat, $rCat) { ?>
<ul>
	<? foreach ($cat->children as $child): ?>
	<li cat-id="<?= $child->id ?>"><span><?= $child->title ?></span> <a class="delete">&times;</a>
		<? $rCat($child, $rCat) ?>
	</li>
	<? endforeach; ?>
</ul>
<? };?>
<div id="categories"><? $rCat($cats, $rCat); ?></div>

<form id="category-form">
	<input type="text" size="40" />
	<input type="submit" value="<?= __('Create new category') ?>" />
</form>


<script src="/files/jquery/jquery.js"   ></script>
<script src="/files/jquery/jquery-ui.js"></script>
<script>
$.fn.index = function () {
	return this.prevAll().length + 1;;
};

$(function () {
	var $cats  = $('#categories');
	$cats.list = $cats.children('ul');

	var $form = $('#category-form');
	var $text = $form.find('[type=text]');

	var cat2li = function (cat) {
		return '<li cat-id="{id}"><span>{title}</span> <a class="delete">&times;</a><ul></ul></li>'
			.replace(/{([a-z]+)}/ig, function ($0, $1) {
				return cat[$1];
			});
	};

	$form.submit(function () {
		if ($text.val()) {
			$.getJSON('/categories/add', { title : $text.val() }, function (res) {
				$cats.list.append(cat2li(res));
				initSortable(true);
			});
		}
		return false;
	});

	$cats.delegate(".delete", "click", function(){
		var elem = $($(this).parents('li').get(0));
		var data = { id : elem.attr('cat-id') };
		if (confirm('Do you really wants to delete this branch? ')) {
			$.getJSON('/categories/delete', data, function (res) {
				if (res.deleted) {
					elem.slideUp();
				} else {
					alert('only admin can delete that');
				}
			});
		}
	});

	var initSortable = function (destroy) {
		if (destroy) $cats.sort.sortable('destroy');
		$cats.sort = $cats.find('ul').sortable({
			stop : function (event, ui) {
				var data = {
					parent_id : 1 * (ui.item.parents('li').attr('cat-id') || 0),
					cat_id    : 1 *  ui.item.attr('cat-id'),
					position  : 1 *  ui.item.index()
				};
				$.getJSON('/categories/move', data, function () {});
			},
			placeholder : 'placeholder',
			connectWith : 'ul'
		});
	};
	initSortable();
});
</script>