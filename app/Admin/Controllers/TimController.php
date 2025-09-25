<?php

namespace App\Admin\Controllers;

use App\Models\Gaji;
use App\Models\Project;
use App\Models\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class TimController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Tim(), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('nama');
            $grid->column('no_telp');
            $grid->column('atm');
            $grid->column('norek');
             $grid->column('gaji', 'Gaji')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $id = $actions->getKey();
                $actions->append("<a href='".url("admin/tim/$id/slip")."' target='_blank' class='btn btn-sm btn-primary'>
                    <i class='feather icon-file-text'></i> Slip Gaji
                </a>");
             });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id', 'Nama Karyawan')
                    ->select(Tim::pluck('nama', 'id'));
                $filter->between('gaji', 'Range Gaji');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Tim(), function (Show $show) {
            // $show->field('id');
            $show->field('nama');
            $show->field('no_telp');
            $show->field('atm');
            $show->field('norek');
            $show->field('gaji')->as(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $show->field('created_at');
            $show->field('updated_at');

            // Tambahkan rincian gaji per bulan
        $show->relation('gajis', 'Rincian Gaji Per Bulan', function ($model) {
            $grid = new \Dcat\Admin\Grid(new \App\Models\Gaji());

            $grid->model()
            ->where('id_tim', $model->id)
            ->with('project');

            $grid->column('tanggal', 'Tanggal')->display(function ($v) {
                return $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-';
            });
            $grid->column('project.nama_project', 'Project');
            $grid->column('jumlah', 'Jumlah')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });

            $grid->disableCreateButton();
            $grid->disableRowSelector();
            $grid->disableActions();
            $grid->disablePagination();

            return $grid;
        });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Tim(), function (Form $form) {
            $form->display('id');
            $form->text('nama')->required();
            $form->mobile('no_telp')->required();
            $form->text('atm')->required();
            $form->text('norek')->required();
            // $form->currency('gaji', 'Gaji')
            //     ->symbol('Rp');


            $form->display('created_at');
            $form->display('updated_at');
        });
    }
        public function slip($id)
    {
        $tim = \App\Models\Tim::with(['gajis.project'])->findOrFail($id);

        // data gaji per bulan
        $gajis = $tim->gajis()->with('project')->get();

        $pdf = Pdf::loadView('pdf.slip-gaji', compact('tim', 'gajis'))
                ->setPaper('A4', 'portrait');

        return $pdf->stream("slip-gaji-{$tim->nama}.pdf");
    }
}
