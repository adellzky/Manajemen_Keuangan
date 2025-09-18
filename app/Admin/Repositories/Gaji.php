<?php

namespace App\Admin\Repositories;

use App\Models\Gaji as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Gaji extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
