<?php
defined('ABSPATH') or exit;

?>
<div class="memento-button">
    <a href="<?= site_url('/?memento_login_action=' . urlencode($_SERVER['REQUEST_URI'])); ?>">Sign in with Memento</a>
</div>
