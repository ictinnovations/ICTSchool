<?php
namespace App\Models;
class DormitoryFee extends \Eloquent {
	protected $table = 'dormitory_fee';
	protected $fillable = ['regiNo','feeMonth','feeAmount'];
}
