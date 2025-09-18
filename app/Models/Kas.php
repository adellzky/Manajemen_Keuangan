<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{

    use HasFactory;
    protected $table = 'kas';

    protected $fillable = [
        'id_project',
        'jumlah',
        'tanggal',
        'keterangan',
        'saldo_akhir',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }

}
