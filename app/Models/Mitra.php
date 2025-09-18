<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
     use HasFactory;

    protected $table = 'mitra';
    protected $fillable = [
        'instansi',
        'nama',
        'alamat',
        'email',
        'telepon',
    ];

    public function pendapatan()
    {
        return $this->hasMany(Pendapatan::class, 'id_mitra');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'id_mitra');
    }
}
