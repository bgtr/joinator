<?php

class Question extends AppModel {
    public $name = 'Question';

    public $hasMany = array(
        'Choices' => array(
            'className' => 'Choice',
        )
    );
}
