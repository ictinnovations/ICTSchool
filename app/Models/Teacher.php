<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Teacher extends Model
{
	use HasFactory;
	protected $table = 'teacher';
	public $timestamps = false;
	protected $fillable = [
		'firstName',
		'lastName',
		'gender',
		'religion',
		'bloodgroup',
		'nationality',
		'dob',
		'photo',
		'phone',
		'email',
		'fatherName',
		'fatherCellNo',
		'presentAddress',
		'parmanentAddress'
	];

	protected $primaryKey = 'id';
}
