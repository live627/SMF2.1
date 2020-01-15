<?php

if (file_exists('./SSI.php') && !defined('SMF')) {
    $ssi = true;
    require_once('./SSI.php');
} elseif (!defined('SMF')) {
    exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');
}
if (!array_key_exists('db_add_column', $smcFunc)) {
    db_extend('packages');
}

require_once "./vendor/autoload.php";
loadSession();
loadTheme();