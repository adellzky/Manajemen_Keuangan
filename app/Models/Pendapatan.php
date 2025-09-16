<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatan extends Model
{
    use HasFactory;

    protected $table = 'pendapatan';
    protected $primaryKey = 'id_pendapatan';
    protected $fillable = [
        'id_project',
        'sumber',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }
}
