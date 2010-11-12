<div class="error">
	<?= $message ?>
	<ul>
		<? foreach ($errors as $e) { echo "<li>$e</li>"; } ?>
	</ul>
</div>