<?php

class URLNormalizer
{
    public function query($url)
    {
        $js_cmd = 'var URLNormalizer = require(' . json_encode(__DIR__ . '/../stdlibs/url-normalizer.js/url-normalizer.js') . ');console.log(JSON.stringify(URLNormalizer.query(' . json_encode(strval($url)) . ')));';
        $node_cmd = 'node -e ' . escapeshellarg($js_cmd);
        return json_decode(`$node_cmd`);
    }
}
