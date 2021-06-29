<?php
namespace App;
class Message extends \Eloquent {
	protected $table = 'message';
	 public $timestamps = false;
	protected $fillable = ['name','description','recording'];
}
