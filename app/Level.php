<?php
namespace App;
class Level extends \Eloquent {
	protected $table = 'level';
	 public $timestamps = false;
	protected $fillable = ['name','description'];
}
