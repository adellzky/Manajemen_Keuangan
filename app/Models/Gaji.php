<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Gaji extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'gaji';
    protected $fillable = [
        'id_tim',
        'id_project',
        'jumlah',
        'tanggal',
        'metode_bayar',
    ];
     protected $casts = [
        'jumlah' => 'integer',
    ];
      // Relasi ke model Tim
    public function tim()
    {
        return $this->belongsTo(Tim::class, 'id_tim');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'id_project');
    }
}

