<?php

class User extends Pix_Table
{
    public function init()
    {
        $this->_name = 'user';

        $this->_primary = 'user_id';

        $this->_columns['user_id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['user_name'] = array('type' => 'varchar', 'size' => 64);

        $this->addIndex('user_name', array('user_name'), 'unique');
    }
}
