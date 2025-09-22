<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
protected $table = 'pengeluaran';
protected $primaryKey = 'id'; 
public $incrementing = true;
protected $keyType = 'int';


    protected $fillable = [
        'id_project',
        'jumlah',
        'tanggal',
        'keterangan',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }
}
