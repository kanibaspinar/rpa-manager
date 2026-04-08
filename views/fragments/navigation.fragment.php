<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<?php
// Admin-only navigation item for RPA Manager module UI
?>
<li class="<?= $Nav->activeMenu == $idname ? "active" : "" ?>">
    <a href="<?= APPURL."/e/".$idname."/settings" ?>">
        <span class="mdi mdi-cellphone-link menu-icon"></span>
        <span class="label"><?= __("RPA Manager") ?></span>
        <span class="tooltip tippy" data-position="right" data-delay="100" data-arrow="true" data-distance="-1" title="<?= __("RPA Manager") ?>"></span>
    </a>
</li>

