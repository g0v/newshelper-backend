<?php

$pathes = array(
    __DIR__ . '/../../../app/views/index/',
    __DIR__ . '/../../../app/views/common/',
    __DIR__ . '/../../../app/views/helper/',
);


$strings = array();
foreach ($pathes as $path) {
    foreach (glob($path . '*') as $f) {
        $c = file_get_contents($f);
        preg_match_all('#_i18n\([\'"]([^\'"]*)[\'"]\)#', $c, $matches);
        foreach ($matches[1]  as $s) {
            $strings[$s] = true;
        }
    }
}

$lines = array_map('trim', file(__DIR__ . '/../locales/zh-tw.csv'));

$strings = array_keys($strings);
$zh_tw = fopen(__DIR__ . '/../locales/zh-tw.csv', 'a');
$new = fopen(__DIR__ . '/../locales/new-zh-tw.csv', 'w');

foreach ($strings as $s) {
    if (in_array($s, $lines)) {
        continue;
    }
    fputs($zh_tw, $s. "\n");
    fputs($new, $s. "\n");
}
