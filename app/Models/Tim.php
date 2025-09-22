<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Tim extends Model
{
	use HasDateTimeFormatter;

    protected $table = 'tim';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama',
        'no_telp',
        'atm',
        'norek',
        'gaji',
    ];
    
    public function gajis()
    {
        return $this->hasMany(\App\Models\Gaji::class, 'id_tim');
    }

}
