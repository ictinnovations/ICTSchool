<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{

	use HasFactory;
	protected $table = 'Student';
	protected $fillable = [
		'regiNo',
		'firstName',
		'lastName',
		'middleName',
		'gender',
		'religion',
		'bloodgroup',
		'nationality',
		'dob',
		'session',
		'class',
		'photo',
		'fatherName',
		'fatherCellNo',
		'motherName',
		'motherCellNo',
		'presentAddress',
		'parmanentAddress'
	];

	protected $primaryKey = 'id';
	public function attendance()
	{
		$this->primaryKey = "regiNo";
		return $this->hasMany('App\Attendance', 'regiNo');
	}
}
