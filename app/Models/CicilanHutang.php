<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class CicilanHutang extends Model
{
    protected $table = 'cicilan_hutang';
    protected $fillable = [
        'id_hutang',
        'id_gaji',
        'nominal_cicilan',
        'tanggal_bayar',
        'keterangan'
    ];

    public function hutang()
    {
        return $this->belongsTo(Hutang::class, 'id_hutang');
    }

    public function gaji()
    {
        return $this->belongsTo(Gaji::class, 'id_gaji');
    }
}
