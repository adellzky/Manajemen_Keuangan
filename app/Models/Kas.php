<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{

    use HasFactory;
    protected $table = 'kas';
    protected $fillable = [
        'jumlah',
        'tanggal',
        'keterangan',
        'saldo_akhir',
    ];

    public function pendapatan()
    {
        return $this->hasMany(Pendapatan::class, 'id_project', 'id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_project', 'id');
    }

    public function gaji()
    {
        return $this->hasMany(Gaji::class, 'id_project', 'id');
    }

}
