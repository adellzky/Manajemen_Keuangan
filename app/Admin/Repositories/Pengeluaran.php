<?php

namespace App\Admin\Repositories;

use App\Models\Pengeluaran as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Pengeluaran extends EloquentRepository
{
    protected $eloquentClass = Model::class;
}
