<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Pendapatan;
use App\Models\Project;
use App\Models\Mitra;
use App\Models\Pendapatan as PendapatanModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;

class PendapatanController extends AdminController
{
    protected function grid()
    {
        return Grid::make(Pendapatan::with(['project', 'mitra']), function (Grid $grid) {
            $grid->column('project.nama_project', 'Nama Project');
            $grid->column('mitra.instansi', 'Mitra');
            $grid->column('jumlah')->display(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $grid->column('tanggal');
            $grid->column('keterangan');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->like('project.nama_project', 'Nama Project');
                $filter->like('mitra.instansi', 'Mitra');
                $filter->between('tanggal', 'Tanggal')->date();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append('<a href="'.url('admin/pendapatan/pdf').'" target="_blank" class="btn btn-sm btn-primary">Cetak PDF</a>');
            });
        });
    }

    protected function detail($id)
    {
        return Show::make($id, new Pendapatan(), function (Show $show) {
            $show->field('project.nama_project', 'Nama Project');
            $show->field('mitra.instansi', 'Mitra');
            $show->field('jumlah')->as(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $show->field('tanggal');
            $show->field('keterangan');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    protected function form()
    {
        return Form::make(new Pendapatan(), function (Form $form) {
            $form->select('id_project', 'Project')
                ->options(Project::pluck('nama_project', 'id')->toArray())
                ->required();

            $form->select('id_mitra', 'Mitra')
                ->options(Mitra::pluck('instansi', 'id')->toArray())
                ->required();

            $form->currency('jumlah', 'Jumlah')->symbol('Rp')->required();
            $form->date('tanggal');
            $form->text('keterangan');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    public function exportPdf()
    {
        $query = PendapatanModel::with(['project', 'mitra']);

        if (request()->filled('project.nama_project')) {
            $query->whereHas('project', function ($q) {
                $q->where('nama_project', 'like', '%' . request('project.nama_project') . '%');
            });
        }

        if (request()->filled('mitra.instansi')) {
            $query->whereHas('mitra', function ($q) {
                $q->where('instansi', 'like', '%' . request('mitra.instansi') . '%');
            });
        }

        if (request()->filled('tanggal.start') && request()->filled('tanggal.end')) {
            $query->whereBetween('tanggal', [request('tanggal.start'), request('tanggal.end')]);
        }

        $data = $query->get();

        $pdf = Pdf::loadView('pdf.pendapatan', compact('data'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-pendapatan.pdf');
    }
}
