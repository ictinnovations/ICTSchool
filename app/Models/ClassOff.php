<?php
namespace App\Models;
class ClassOff extends \Eloquent {
    protected $table = 'ClassOffDay';

    protected $dates=['offDate'];

    protected $fillable = [
        'offDate',
        'description',
        'oType',
        'status',
    ];

}
