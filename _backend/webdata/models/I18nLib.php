<?php

class I18nLib
{
    public static function isValidLocale($l)
    {
        if (in_array($l, array('zh-tw', 'zh-hk', 'zh', 'zh-cn'))) {
            return 'zh-tw';
        }

        if (strpos($l, 'en') === 0) {
            return 'en';
        }

        return null;
    }

    public static function getCurrentLocale()
    {
        // GET 參數優先
        if (array_key_exists('locale', $_GET) and $l = self::isValidLocale($_GET['locale'])) {
            return $l;
        }

        // 瀏覽器設定優先
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER) and $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
            $locales = explode(',', trim($_SERVER['HTTP_ACCEPT_LANGUAGE']));
            foreach ($locales as $locale) {
                $locale = preg_replace('#;.*$#', '', $locale);
                if ($l = self::isValidLocale($locale)) {
                    return $l;
                }
            }
        }

        // TODO: geoip 認 IP
        return 'en';
    }

    protected static $locale_maps = array();

    public static function getLocaleMap($locale)
    {
        if (array_key_exists($locale, self::$locale_maps)) {
            return self::$locale_maps[$locale];
        }

        if (!file_exists(__DIR__ . '/../locales/' . $locale . '.csv')) {
            return array();
        }

        $fp = fopen(__DIR__ . '/../locales/' . $locale . '.csv', 'r');
        $columns = fgetcsv($fp);
        $ret = array();
        while ($rows = fgetcsv($fp)) {
            if (trim($rows[1])) {
                $ret[$rows[0]] = $rows[1];
            }
        }
        fclose($fp);
        return self::$locale_maps[$locale] = $ret;
    }

    public static function i18n($str)
    {
        $locale = self::getCurrentLocale();
        if ($locale == 'zh-tw') {
            return $str;
        }
        $locale_map = self::getLocaleMap($locale);
        if (array_key_Exists($str, $locale_map)) {
            return $locale_map[$str];
        }

        $en_map = self::getLocaleMap('en');
        if (array_key_Exists($str, $en_map)) {
            return $en_map[$str];
        }

        return $str;
    }
}
