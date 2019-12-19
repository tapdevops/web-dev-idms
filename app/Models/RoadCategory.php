<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoadCategory extends Model
{
    use SoftDeletes;
    
	
	protected $table = 'TM_ROAD_CATEGORY';
    protected $dates =['deleted_at'];
	
	protected $fillable = [
		'status_id',
		'updated_by',
		'category_name',
		'category_code',
		'category_initial',
	];
}
