<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<?php
// Navigation item for non-admin users, pointing to the core /realphone route.
?>
<li class="<?= $Nav->activeMenu == 'realphone' ? 'active' : '' ?>">
    <a href="<?= APPURL."/realphone" ?>">
        <span class="mdi mdi-cellphone-cog menu-icon"></span>
        <span class="label"><?= __("Real Phone Manager") ?></span>
        <span class="tooltip tippy" data-position="right" data-delay="100" data-arrow="true" data-distance="-1" title="<?= __("Real Phone Manager") ?>"></span>
    </a>
</li>

