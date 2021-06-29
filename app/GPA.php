<?php
namespace App;
class GPA extends \Eloquent {

	protected $table = 'GPA';
protected $fillable = ['for','gpa','grade','markfrom','markto'];
}
