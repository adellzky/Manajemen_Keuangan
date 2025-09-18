<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Kas;
use App\Models\Project;
use App\Models\Kas as KasModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class KasController extends AdminController
{
    /**
     * Grid untuk data Kas
     */
    protected function grid()
    {
        return Grid::make(new Kas(), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('project.nama_project', 'Project');
            $grid->column('jumlah', 'Jumlah')
                ->display(fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-');
            $grid->column('saldo_akhir', 'Saldo Akhir')
                ->display(fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-');
            $grid->column('tanggal', 'Tanggal')->sortable();
            $grid->column('keterangan', 'Keterangan');
            $grid->column('created_at', 'Dibuat');
            $grid->column('updated_at', 'Diubah')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id_project', 'Project')
                    ->select(Project::pluck('nama_project', 'id')->toArray());
                $filter->between('tanggal', 'Tanggal')->date();
            });

            $grid->paginate(10);
        });
    }

    /**
     * Detail view untuk Kas
     */
    protected function detail($id)
    {
        return Show::make($id, new Kas(), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('project.nama_project', 'Project');
            $show->field('jumlah', 'Jumlah')
                ->as(fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-');
            $show->field('saldo_akhir', 'Saldo Akhir')
                ->as(fn($v) => $v !== null ? 'Rp ' . number_format((float) $v, 0, ',', '.') : '-');
            $show->field('tanggal', 'Tanggal');
            $show->field('keterangan', 'Keterangan');
            $show->field('created_at', 'Dibuat');
            $show->field('updated_at', 'Diubah');
        });
    }

    /**
     * Form untuk tambah/edit Kas
     */
    protected function form()
    {
        return Form::make(new Kas(), function (Form $form) {
            $form->display('id', 'ID');

            $form->select('id_project', 'Project')
                ->options(Project::pluck('nama_project', 'id')->toArray())
                ->required();

            // Input jumlah dengan currency
            $form->currency('jumlah', 'Jumlah')
                ->symbol('Rp')
                ->required();

            $form->date('tanggal', 'Tanggal')
                ->default(now())
                ->required();

            $form->textarea('keterangan', 'Keterangan');

            $form->display('saldo_akhir', 'Saldo Akhir (otomatis)');

            // Hitung saldo_akhir kumulatif per project sebelum simpan
            $form->saving(function (Form $form) {
                $projectId = $form->id_project;
                $jumlah = (float) $form->jumlah;

                // Total kas project sebelum record baru
                $totalKasSebelumnya = (float) KasModel::where('id_project', $projectId)->sum('jumlah');

                // Saldo akhir kumulatif = totalKasSebelumnya + jumlah baru
                $form->saldo_akhir = $totalKasSebelumnya + $jumlah;
            });

            $form->display('created_at', 'Dibuat');
            $form->display('updated_at', 'Diubah');
        });
    }
}
