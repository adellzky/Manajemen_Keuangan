<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama_klien',
        'nama_project',
        'deskripsi',
        'harga',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    // Relasi
    public function pendapatan()
    {
        return $this->hasMany(Pendapatan::class, 'id_project');
    }

    // public function pengeluaran()
    // {
    //     return $this->hasMany(Pengeluaran::class, 'id_project');
    // }

    // public function kas()
    // {
    //     return $this->hasMany(Kas::class, 'id_project');
    // }

    // public function gaji()
    // {
    //     return $this->hasMany(Gaji::class, 'id_project');
    // }
}
