<?php

class AnswerVote extends Pix_Table
{
    public function init()
    {
        $this->_name = 'answer_vote';
        $this->_primary = array('answer_id', 'user_id');

        $this->_columns['answer_id'] = array('type' => 'int');
        $this->_columns['user_id'] = array('type' => 'int');
        $this->_columns['score'] = array('type' => 'tinyint');
        $this->_columns['vote_at'] = array('type' => 'int');
        $this->_columns['vote_from'] = array('type' => 'int', 'unsigned' => true);

        $this->_relations['answer'] = array('rel' => 'has_one', 'type' => 'Answer', 'foreign_key' => 'answer_id');
        $this->_relations['user'] = array('rel' => 'has_one', 'type' => 'User', 'foreign_key' => 'user_id');
    }
}
