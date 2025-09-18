<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Tim extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'tim';
    protected $PrimaryKey = 'id';
    protected $fillable = [
        'nama',
        'no_telp',
        'atm',
        'norek',
        'gaji',
    ];

}
