<?php
namespace App\Models;
class AccountSector extends \Eloquent {
	protected $table = 'accounting_sector';
	protected $fillable = ['name','type'];
}
