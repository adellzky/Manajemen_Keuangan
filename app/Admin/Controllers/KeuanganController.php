<?php

namespace App\Admin\Controllers;

use App\Models\Gaji;
use App\Models\Kas;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class KeuanganController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Project(), function (Grid $grid) {
            // Eager-aggregate sums (hindari N+1)
            $grid->model()
                ->withSum('pendapatan', 'jumlah')
                ->withSum('pengeluaran', 'jumlah')
                ->withSum('gaji', 'jumlah');

            $grid->column('id', 'ID')->sortable();
            $grid->column('nama_project', 'Nama Project');

            $grid->column('pendapatan_sum_jumlah', 'Total Pendapatan')
                ->display(fn($val) => number_format($val ?? 0, 0, ',', '.'));

            $grid->column('pengeluaran_sum_jumlah', 'Total Pengeluaran')
                ->display(fn($val) => number_format($val ?? 0, 0, ',', '.'));

            $grid->column('gaji_sum_jumlah', 'Gaji')
                ->display(fn($val) => number_format($val ?? 0, 0, ',', '.'));


            // Sisa = (Pendapatan + Kas) - (Pengeluaran + Gaji)
            $grid->column('sisa', 'Saldo')->display(function () {
                $pendapatan  = (float) ($this->pendapatan_sum_jumlah  ?? 0);
                $pengeluaran = (float) ($this->pengeluaran_sum_jumlah ?? 0);
                $gaji        = (float) ($this->gaji_sum_jumlah        ?? 0);

                $sisa = $pendapatan - ($pengeluaran + $gaji);

                return number_format($sisa, 0, ',', '.');
            })->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                 $filter->panel()->expand(false);
                 $filter->equal('id', 'Nama Project')
                        ->select(Project::pluck('nama_project', 'id'));
            });

            $grid->disableActions();     // sembunyikan tombol action
            $grid->disableCreateButton(); // sembunyikan tombol + New

        });
    }


    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */

    /**
     * Make a form builder.
     *
     * @return Form
     */
    //  protected function form()
    // {
    //     return null;
    // }
}
