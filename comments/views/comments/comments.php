<dl class="comments">
	<? foreach ($comments as $comment): ?>
	<dt><?= $comment->get_author_name();?> <i>(<?= $comment->created ?>)</i>:</dt>
	<dd><?= $comment->content ?></dd>
	<? endforeach; ?>
</dl>