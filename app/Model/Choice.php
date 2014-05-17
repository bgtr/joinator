<?php

class Choice extends AppModel {
    public $name = 'Choice';

    public $belongsTo = array(
        'Question' => array(
            'className'    => 'Question',
            'foreignKey'   => 'question_id'
        )
    );
}
