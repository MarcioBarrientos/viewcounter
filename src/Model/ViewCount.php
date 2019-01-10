<?php

namespace Chillu\ViewCount\Model;

use SilverStripe\ORM\DataObject;

class ViewCount extends DataObject
{
    private static $table_name = 'ViewCount';

    private static $db = [
        'Count' => 'Int',
        'RecordID' => 'Int',
        'RecordClass' => 'Varchar(255)'
    ];

    private static $indexes = [
        'RecordIDRecordClass' => [
            'type' => 'index',
            'columns' => ['RecordID','RecordClass']
        ],
        'RecordIDRecordClassUnique' => [
            'type' => 'unique',
            'columns' => ['RecordID','RecordClass']
        ]
    ];

    /**
     * @return DataObject
     */
    public function getRecord()
    {
        $class = $this->RecordClass;
        return $class::get()->byID($this->RecordID)->First();
    }
}
