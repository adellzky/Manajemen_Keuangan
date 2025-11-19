<?php

namespace App\Admin\Controllers;

use App\Models\Gaji;
use App\Models\Kas;
use App\Models\Pendapatan;
use App\Models\Pengeluaran;
use App\Models\Project;
use Carbon\Carbon;
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
                ->display(function ($val) {
                    return number_format($val ?? 0, 0, ',', '.');
                })
                ->expand(function () {

                    $pendapatan = Pendapatan::where('id_project', $this->id)
                        ->orderBy('tanggal', 'desc')
                        ->get();

                    if ($pendapatan->isEmpty()) {
                        return "<p style='padding:8px'>Tidak ada data pendapatan.</p>";
                    }

                    $html = "<h4>Detail Pendapatan</h4>
                    <table class='table table-sm'>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>";

                    foreach ($pendapatan as $item) {
                        $html .= "
                            <tr>
                                <td>" . Carbon::parse($item->tanggal)->format('d-m-Y') . "</td>
                                <td>Rp " . number_format($item->jumlah, 0, ',', '.') . "</td>
                                <td>" . ($item->keterangan ?: '-') . "</td>
                            </tr>";
                    }

                    $html .= "</tbody></table>";

                    return $html;
            });

            $grid->column('pengeluaran_sum_jumlah', 'Total Pengeluaran')
                ->display(function ($val) {
                    return number_format($val ?? 0, 0, ',', '.');
                })
                ->expand(function () {

                    $pengeluaran = Pengeluaran::where('id_project', $this->id)
                        ->orderBy('tanggal', 'desc')
                        ->get();

                    if ($pengeluaran->isEmpty()) {
                        return "<p style='padding:8px'>Tidak ada data pengeluaran.</p>";
                    }

                    $html = "<h4>Detail Pengeluaran</h4>
                    <table class='table table-sm'>
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah</th>
                                <th>Sumber Dana</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>";

                    foreach ($pengeluaran as $item) {
                        $html .= "
                            <tr>
                                <td>" . Carbon::parse($item->tanggal)->format('d-m-Y') . "</td>
                                <td>Rp " . number_format($item->jumlah, 0, ',', '.') . "</td>
                                <td>" . strtoupper($item->sumber_dana) . "</td>
                                <td>" . ($item->keterangan ?: '-') . "</td>
                            </tr>";
                    }

                    $html .= "</tbody></table>";

                    return $html;
            });

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
