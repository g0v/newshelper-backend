<?php

class ReportChangeLog extends Pix_Table
{
    public function init()
    {
        $this->_name = 'report_change_log';
        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['report_id'] = array('type' => 'int');
        $this->_columns['updated_at'] = array('type' => 'int');
        $this->_columns['updated_from'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['update_by'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['old_values'] = array('type' => 'text');
        $this->_columns['new_values'] = array('type' => 'text');
    }
}
