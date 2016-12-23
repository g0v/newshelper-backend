<?php

if (preg_match('#^/(js|css|fonts|images)/#', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}

if (strpos($_SERVER['REQUEST_URI'], '/i18n.php') === 0) {
    return false;
}
include("_public/index.php");

