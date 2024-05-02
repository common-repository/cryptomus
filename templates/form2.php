<? get_header(); ?>

<div style="display:block; width: 500px; margin: auto;">
	<h1>Cryptomus Payment Gateway</h1>
	<pre>

		<?= print_r(get_query_var('network'), true) ?>
		<?= print_r(get_query_var('to_currency'), true) ?>
	</pre>
</div>
<? get_footer(); ?>
