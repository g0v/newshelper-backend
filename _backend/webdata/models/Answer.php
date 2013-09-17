<?php

class Answer extends Pix_Table
{
    public function init()
    {
        $this->_name = 'answer';
        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'auto_increment' => true);
        $this->_columns['report_id'] = array('type' => 'int');
        $this->_columns['vote_count'] = array('type' => 'tinyint');
        $this->_columns['text'] = array('type' => 'text');
        $this->_columns['created_at'] = array('type' => 'int');
        $this->_columns['created_from'] = array('type' => 'int', 'unsigned' => true);
        $this->_columns['created_by'] = array('type' => 'int');

        $this->_relations['creator'] = array('rel' => 'has_one', 'type' => 'User', 'foreign_key' => 'created_by');
        $this->_relations['report'] = array('rel' => 'has_one', 'type' => 'Report', 'foreign_key' => 'report_id');
        $this->_relations['votes'] = array('rel' => 'has_many', 'type' => 'AnswerVote', 'foreign_key' => 'id');

        $this->addIndex('reportid_votecount', array('report_id', 'vote_count'));
    }
}
