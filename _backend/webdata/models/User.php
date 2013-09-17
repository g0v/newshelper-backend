<?php

class UserRow extends Pix_Table_Row
{
    public function getUserName($full = false)
    {
        if (!preg_match('#google://(.*)@(.*)#', $this->user_name, $matches)) {
            return '#';
        }

        if ('name-only' == $full) {
            return $matches[1];
        } else if ($full) {
            return $matches[1] . '@' . $matches[2];
        } else {
            return substr($matches[1], 0, 3) . '...@' . $matches[2];
        }
        
    }
}

class User extends Pix_Table
{
    public function init()
    {
        $this->_name = 'user';
        $this->_rowClass = 'UserRow';

        $this->_primary = 'user_id';

        $this->_columns['user_id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['user_name'] = array('type' => 'varchar', 'size' => 64);

        $this->addIndex('user_name', array('user_name'), 'unique');
    }
}
