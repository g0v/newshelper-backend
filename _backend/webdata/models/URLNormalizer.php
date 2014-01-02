<?php

class URLNormalizer
{
    protected static $_url_cache = null;

    public static function query($url)
    {
        if (is_null(self::$_url_cache)) {
            self::$_url_cache = array();
        }
        if (array_key_exists($url, self::$_url_cache)) {
            return self::$_url_cache[$url];
        }
        $js_cmd = 'var URLNormalizer = require(' . json_encode(__DIR__ . '/../stdlibs/url-normalizer.js/url-normalizer.js') . ');console.log(JSON.stringify(URLNormalizer.query(' . json_encode(strval($url)) . ')));';
        $node_cmd = 'node -e ' . escapeshellarg($js_cmd);
        return self::$_url_cache[$url] = json_decode(`$node_cmd`);
    }
}
