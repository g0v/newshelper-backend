<?php

class DbSession extends Pix_Table
{
    public function init()
    {
        $this->_name = 'db_session';
        $this->_primary = 'key';

        $this->_columns['key'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['value'] = array('type' => 'text');
        $this->_columns['expired_at'] = array('type' => 'int');
    }

    public static function get($key)
    {
        return ($row = self::find(crc32($key))) ? base64_decode($row->value) : null;
    }

    public static function set($key, $value)
    {
        try {
            self::insert(array(
                'key' => crc32($key),
                'value' => base64_encode($value),
                'expired_at' => time() + 86400
            ));
        } catch (Pix_Table_DuplicateException $e) {
            self::find(crc32($key))->update(array(
                'value' => base64_encode($value),
                'expired_at' => time() + 86400
            ));
        }
        DbSession::search('expired_at < ' . time())->delete();
    }

    public static function delete($key)
    {
        if ($row = self::find(crc32($key))) {
            $row->delete();
        }
        DbSession::search('expired_at < ' . time())->delete();
    }
}
