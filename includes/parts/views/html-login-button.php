<?php
defined('ABSPATH') or exit;

?>
<div>
    <form action="">
        <input type="hidden" name="memento_login_action" value="<?= urlencode($_SERVER['REQUEST_URI']) ?>">
        <button type="submit" class='button button__memento'>Sign in with Memento</button>
    </form>
</div>
