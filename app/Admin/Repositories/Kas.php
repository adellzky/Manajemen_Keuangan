<?php

namespace App\Admin\Repositories;

use App\Models\Kas as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Kas extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;

    /**
     * Ambil semua data kas dengan relasi project.
     */
    public function allWithProject()
    {
        return Model::with('project')->latest()->get();
    }

    /**
     * Cari kas berdasarkan ID.
     */
    public function findById($id)
    {
        return Model::with('project')->findOrFail($id);
    }

    /**
     * Hitung saldo terakhir dari kas.
     */
    public function getLastSaldo()
    {
        return Model::latest()->value('saldo_akhir') ?? 0;
    }

    /**
     * Simpan kas baru dengan perhitungan saldo otomatis.
     */
    public function createWithSaldo(array $data)
    {
        $lastSaldo = $this->getLastSaldo();

        $data['saldo_akhir'] = $lastSaldo + $data['jumlah'];

        return Model::create($data);
    }

    /**
     * Update kas (saldo tetap dihitung ulang).
     */
    public function updateWithSaldo(Model $kas, array $data)
    {
        $lastSaldo = $this->getLastSaldo();

        $data['saldo_akhir'] = ($lastSaldo - $kas->jumlah) + $data['jumlah'];

        $kas->update($data);

        return $kas;
    }
}
