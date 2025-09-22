<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $fillable = [
        'id_mitra',
        'nama_project',
        'kategori',
        'deskripsi',
        'harga',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'status_bayar',
    ];

    // Relasi
    public function mitra()
    {
        return $this->belongsTo(Mitra::class, 'id_mitra');
    }

    public function pendapatan()
    {
        return $this->hasMany(Pendapatan::class, 'id_project', 'id');
    }

    public function pengeluaran()
    {
        return $this->hasMany(Pengeluaran::class, 'id_project', 'id');
    }

    public function kas()
    {
        return $this->hasMany(Kas::class, 'id_project', 'id');
    }

    public function gaji()
    {
        return $this->hasMany(Gaji::class, 'id_project', 'id');
    }

}
