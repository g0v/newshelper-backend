<?php

class Report extends Pix_Table
{
    public function init()
    {
        $this->_name = 'report';
        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['created_at'] = array('type' => 'int', 'default' => 0);
        $this->_columns['updated_at'] = array('type' => 'int', 'default' => 0);
        $this->_columns['deleted_at'] = array('type' => 'int', 'default' => 0);
        $this->_columns['news_title'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['news_link'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['report_link'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['report_title'] = array('type' => 'varchar', 'size' => 255);

        $this->addIndex('updated_at', array('updated_at'));
    }
}
