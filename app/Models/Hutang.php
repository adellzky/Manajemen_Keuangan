<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 'hutang';
    protected $fillable = [
        'id_tim',
        'jumlah_hutang',
        'tanggal_pinjam',
        'sisa_hutang',
        'keterangan',
        'status'
    ];

    public function tim()
    {
        return $this->belongsTo(Tim::class, 'id_tim');
    }

    public function cicilan()
    {
        return $this->hasMany(CicilanHutang::class, 'id_hutang');
    }
}
