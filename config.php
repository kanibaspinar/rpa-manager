<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<?php
    return [
        "idname" => "rpa-manager",
        "plugin_name" => "RPA Manager",
        "author" => "Kani Baspinar",
        "author_uri" => "https://hypervoter.com",
        "plugin_uri" => "https://hypervoter.com",
        "version" => "2.0.0",
        "desc" => "Central manager for real-phone RPA devices and accounts. Provides admin-only configuration for farm API URL and exposes Real Phone Manager navigation for users.",
        "icon_style" => "background-color: #0e9f6e; color: #fff; font-size: 18px;",
        "settings_page_uri" => APPURL . "/e/rpa-manager/settings",
    ];

