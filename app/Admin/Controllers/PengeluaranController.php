<?php

namespace App\Admin\Controllers;

use App\Models\Pengeluaran;
use App\Models\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PengeluaranController extends AdminController
{
    /**
     * Grid builder.
     */
    protected function grid()
    {
        return Grid::make(Pengeluaran::with(['project']), function (Grid $grid) {
            $grid->column('id', 'ID')->sortable();
            $grid->column('project.nama_project', 'Project');
            $grid->column('jumlah', 'Jumlah')->display(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $grid->column('tanggal', 'Tanggal')->sortable();
            $grid->column('keterangan', 'Keterangan');


            // Filter
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id_project', 'Project')
                    ->select(Project::pluck('nama_project', 'id')->toArray());
                $filter->between('tanggal', 'Tanggal')->date();
            });
        });
    }

    /**
     * Detail builder.
     */
    protected function detail($id)
    {
        return Show::make(new Pengeluaran(), function (Show $show) {
            $show->field('id', 'ID');
            $show->field('project', 'Project')->as(function ($project) {
                return $project ? $project->nama_project : '-';
            });
            $show->field('jumlah', 'Jumlah')->as(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $show->field('tanggal', 'Tanggal');
            $show->field('keterangan', 'Keterangan');
            $show->field('created_at', 'Dibuat');
            $show->field('updated_at', 'Diperbarui');
        })->findOrFail($id);
    }



    /**
     * Form builder.
     */
    protected function form()
    {
        return Form::make(new Pengeluaran(), function (Form $form) {
            $form->display('id', 'ID');

            $form->select('id_project', 'Project')
                ->options(Project::pluck('nama_project', 'id')->toArray())
                ->required();

            $form->currency('jumlah', 'Jumlah')
                ->symbol('Rp')
                ->required();

            $form->date('tanggal', 'Tanggal')
                ->default(now())
                ->required();

            $form->textarea('keterangan', 'Keterangan');

            $form->display('created_at', 'Dibuat');
            $form->display('updated_at', 'Diperbarui');

            // Hook setelah data tersimpan
            $form->saved(function (Form $form, $result) {
                $pengeluaran = $form->model();

                // hitung total pengeluaran untuk project terkait
                $total = \App\Models\Pengeluaran::where('id_project', $pengeluaran->id_project)
                    ->sum('jumlah');

                // update hanya record yang baru saja dibuat/diupdate
                $pengeluaran->update(['total_pengeluaran' => $total]);
            });
        });
    }

}
