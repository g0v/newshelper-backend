<?php

class ReportChangeLogRow extends Pix_Table_Row
{
    public function getOldDiffValues()
    {
        $old_values = json_decode($this->old_values);
        $new_values = json_decode($this->new_values);
        $ret = new StdClass;
        foreach ($old_values as $k => $v) {
            if (!property_exists($new_values, $k) or $new_values->{$k} !== $old_values->{$k}) {
                $ret->{$k} = $v;
            }
        }
        return $ret;
    }

    public function getNewDiffValues()
    {
        $old_values = json_decode($this->old_values);
        $new_values = json_decode($this->new_values);
        $ret = new StdClass;
        foreach ($new_values as $k => $v) {
            if (!$old_values or !property_exists($old_values, $k) or $new_values->{$k} !== $old_values->{$k}) {
                $ret->{$k} = $v;
            }
        }
        return $ret;
    }
}

class ReportChangeLog extends Pix_Table
{
    public function init()
    {
        $this->_name = 'report_change_log';
        $this->_primary = 'id';
        $this->_rowClass = 'ReportChangeLogRow';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['report_id'] = array('type' => 'int');
        $this->_columns['updated_at'] = array('type' => 'int');
        $this->_columns['updated_from'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['updated_by'] = array('type' => 'varchar', 'size' => 255);
        $this->_columns['old_values'] = array('type' => 'text');
        $this->_columns['new_values'] = array('type' => 'text');
    }
}
