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
        'total_potongan_cicilan',
    ];

    public function gajis()
    {
        return $this->hasMany(\App\Models\Gaji::class, 'id_tim');
    }

    public function cicilanHutang()
{
    return $this->hasManyThrough(
        \App\Models\CicilanHutang::class,
        \App\Models\Hutang::class,
        'id_tim',      // foreign key di tabel hutang
        'id_hutang',   // foreign key di tabel cicilan_hutang
        'id',          // local key di tabel tim
        'id'           // local key di tabel hutang
    );
}

}
