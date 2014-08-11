<dl class="comments">
	<?php foreach ($comments as $comment): ?>
	<dt><?= $comment->get_author_name();?> <i>(<?= $comment->created ?>)</i>:</dt>
	<dd><?= $comment->content ?></dd>
	<?php endforeach; ?>
</dl>
