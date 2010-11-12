<? $lang = function ($index) {
	return __(Kohana::message('comments', $index));
}; ?>

<div class="comments-form">
	<h2><?= $lang('title') ?></h2>
	<?= $errors ?>
	<?= Form::open() ?>
		<? if ($show_username_field): ?>
			<?= Form::label('username', $lang('fields.username') . ':') ?>
			<?= Form::input('comment-username', $comment->username, array('class' => 'text')) ?>
		<? endif; ?>
		<?= Form::label('content', $lang('fields.content') . ':') ?>
		<?= Form::textarea('comment-content', $comment->content) ?>
		<?= Form::submit(null, $lang('submit'), array('class' => 'submit')) ?>
	<?= Form::close() ?>
</div>