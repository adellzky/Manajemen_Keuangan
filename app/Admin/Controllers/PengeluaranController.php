<?php

namespace App\Admin\Controllers;

use App\Models\Pengeluaran;
use App\Models\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;

class PengeluaranController extends AdminController
{
    protected function grid()
    {
        return Grid::make(new Pengeluaran(), function (Grid $grid) {
            $grid->column('project.nama_project', 'Project')->display(function ($val) {
                return $this->id_project ? $val : ' -';
            });

            $grid->column('nama_project_manual', 'Nama Pengeluaran Lain')->display(function ($val) {
                return $val ?? ' -';
            });

            $grid->column('jumlah', 'Jumlah')->display(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $grid->column('tanggal', 'Tanggal')->sortable();
            $grid->column('keterangan', 'Keterangan');
            $grid->column('created_at', 'Dibuat');
            $grid->column('updated_at', 'Diperbarui')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id_project', 'Project')->select(Project::pluck('nama_project', 'id')->toArray());
                $filter->between('tanggal', 'Tanggal')->date();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append('<a href="'.url('admin/pengeluaran/pdf').'" target="_blank" class="btn btn-sm btn-primary">Cetak PDF</a>');
            });
        });
    }

    protected function detail($id)
    {
        return Show::make($id, new Pengeluaran(), function (Show $show) {
            $show->field('project.nama_project', 'Project')->as(fn($val) => $val ?? '-');
            $show->field('nama_project_manual', 'Nama Pengeluaran Lain')->as(fn($val) => $val ?? '-');
            $show->field('jumlah', 'Jumlah')->as(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $show->field('tanggal', 'Tanggal');
            $show->field('keterangan', 'Keterangan');
            $show->field('created_at', 'Dibuat');
            $show->field('updated_at', 'Diperbarui');
        });
    }

    protected function form()
    {
        return Form::make(new Pengeluaran(), function (Form $form) {
            $form->hidden('id');

            $projects = Project::pluck('nama_project', 'id')->toArray();
            $form->select('id_project', 'Pilih Project')
                ->options(['' => '-- Kosong = Pengeluaran Umum --'] + $projects)
                ->default('')
                ->placeholder('-- Pilih Project (opsional) --');

            $form->text('nama_project_manual', 'Nama Pengeluaran Lain')->help('Isi field ini jika tidak ada project yang dipilih.');
            $form->currency('jumlah', 'Jumlah')->symbol('Rp')->required();
            $form->date('tanggal', 'Tanggal')->default(now())->required();
            $form->textarea('keterangan', 'Keterangan');
            $form->display('created_at', 'Dibuat');
            $form->display('updated_at', 'Diperbarui');
        });
    }

    public function exportPdf()
    {
        $data = Pengeluaran::with('project')->get();

        $pdf = Pdf::loadView('pdf.pengeluaran', compact('data'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pengeluaran.pdf');
    }
}
