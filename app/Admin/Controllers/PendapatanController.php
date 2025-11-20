<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Pendapatan;
use App\Models\Project;
use App\Models\Mitra;
use App\Models\Pendapatan as PendapatanModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Admin; 
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;

class PendapatanController extends AdminController
{
    protected function grid()
    {
        return Grid::make(Pendapatan::with(['project', 'mitra']), function (Grid $grid) {
            $grid->column('tanggal');
            $grid->column('project.nama_project', 'Nama Project');
            $grid->column('mitra.instansi', 'Mitra');
            $grid->column('jumlah')->display(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $grid->column('keterangan');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id_project', 'Nama Project')
                    ->select(Project::pluck('nama_project', 'id'));
                $filter->equal('id_mitra', 'Mitra')
                    ->select(Mitra::pluck('instansi', 'id'));
                $filter->between('tanggal', 'Tanggal')->date();
            });

            $grid->tools(function (Grid\Tools $tools) {
                $query = request()->getQueryString();
                $url = url('admin/pendapatan/pdf') . ($query ? '?' . $query : '');
                $tools->append('<a href="'.$url.'" target="_blank" class="btn btn-sm btn-primary">Cetak PDF</a>');
            });
        });
    }

    protected function detail($id)
    {
        return Show::make($id, new Pendapatan(), function (Show $show) {
            $show->field('tanggal');
            $show->field('project.nama_project', 'Nama Project');
            $show->field('mitra.instansi', 'Mitra');
            $show->field('jumlah')->as(fn($val) => 'Rp ' . number_format($val, 0, ',', '.'));
            $show->field('keterangan');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    protected function form()
    {
        return Form::make(new Pendapatan(), function (Form $form) {
            $projectMap = Project::pluck('id_mitra', 'id'); 
            $projectMapJson = json_encode($projectMap);
            $script = <<<JS
                var projectToMitra = {$projectMapJson};
                $('select[name="id_project"]').on('change', function() {
                    var projectId = $(this).val();
                    if (projectId && projectToMitra[projectId]) {
                        $('select[name="id_mitra"]').val(projectToMitra[projectId]).trigger('change');
                    }
                });
            JS;

            Admin::script($script);

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

        if (request()->filled('id_project')) {
            $query->where('id_project', request('id_project'));
        }

        if (request()->filled('id_mitra')) {
            $query->where('id_mitra', request('id_mitra'));
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