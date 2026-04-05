<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryStat extends Model
{
    protected $primaryKey = 'country_code';
    public $incrementing  = false;
    protected $keyType    = 'string';
}