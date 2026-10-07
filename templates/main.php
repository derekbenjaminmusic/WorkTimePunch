<?php

declare(strict_types=1);

/** @var array $_ */
?>
<div id="worktimepunch-root" class="worktimepunch-page">
	<h2>WorkTimePunch</h2>
	<?php if ($_['worktimeEnabled']): ?>
		<p><?php p($l->t('WorkTime is enabled. The buttons in the Nextcloud top bar are loaded for logged-in users.')); ?></p>
	<?php else: ?>
		<p><?php p($l->t('WorkTime is not enabled. WorkTimePunch will disable itself.')); ?></p>
	<?php endif; ?>
</div>
