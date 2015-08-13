<div class="error">
	<p>Square error: Your environment doesn't meet the system requirements listed below. No functionality was loaded.</p>

	<ul class="ul-disc">
		<?php if ($this->is_problem_php) : ?>
			<li>
				<strong>Having PHP <?= $this->required_php_version; ?>+</strong>
				<em>(You're running version <?= PHP_VERSION; ?>)</em>
			</li>
		<?php else : ?>
			<li>
				<strong>Having WordPress <?= $this->required_wp_version; ?>+</strong>
				<em>(You're running version <?= esc_html($wp_version); ?>)</em>
			</li>
		<?php endif; ?>
	</ul>

	<p>If you need to upgrade your version of PHP you can ask your hosting company for assistance, and if you need help upgrading WordPress you can refer to <a href="http://codex.wordpress.org/Upgrading_WordPress">the Codex</a>.</p>
</div>
