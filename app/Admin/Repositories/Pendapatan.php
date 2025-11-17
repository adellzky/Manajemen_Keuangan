<?php

namespace App\Admin\Repositories;

use App\Models\Pendapatan as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Pendapatan extends Model
{
    protected $table = 'pendapatan';
    protected $primaryKey = 'id';
}

