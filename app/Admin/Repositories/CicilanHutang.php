<?php

namespace App\Admin\Repositories;

use App\Models\CicilanHutang as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CicilanHutang extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
