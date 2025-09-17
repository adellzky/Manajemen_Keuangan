<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Pendapatan;
use App\Models\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PendapatanController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Pendapatan::with(['project']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('project.nama_project', 'Nama Project');
            $grid->column('sumber');
            $grid->column('jumlah')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $grid->column('tanggal');
            $grid->column('keterangan');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->like('project.nama_project', 'Nama Project');
                $filter->like('sumber', 'Sumber');
                $filter->between('tanggal', 'Tanggal')->date();
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
        return Show::make($id, new Pendapatan(), function (Show $show) {
            $show->field('id');
            $show->field('id_project');
            $show->field('sumber');
            $show->field('jumlah');
            $show->field('tanggal');
            $show->field('keterangan');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Pendapatan(), function (Form $form) {
            $form->display('id');
            $form->select('id_project', 'Project')
                ->options(Project::pluck('nama_project', 'id'))
                ->required();
            $form->text('sumber');
            $form->currency('jumlah', 'Jumlah')
                ->symbol('Rp')
                ->required();
            $form->date('tanggal');
            $form->text('keterangan');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
