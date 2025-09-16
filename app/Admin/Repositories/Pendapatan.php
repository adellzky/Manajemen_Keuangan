<?php

namespace App\Admin\Repositories;

use App\Models\Pendapatan as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Pendapatan extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
