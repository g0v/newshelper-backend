<?php

$pathes = array(
    __DIR__ . '/../../../app/views/index/',
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

$strings = array_keys($strings);
$output = fopen(__DIR__ . '/../locales/zh-tw.csv', 'w');
fputcsv($output, array("中文", "翻譯"));
foreach ($strings as $s) {
    fputcsv($output, array($s, ''));
}
