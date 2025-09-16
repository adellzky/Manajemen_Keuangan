<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ProjectController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Project(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('nama_klien');
            $grid->column('nama_project');
            $grid->column('deskripsi');
            $grid->column('harga');
            $grid->column('tanggal_mulai');
            $grid->column('tanggal_selesai');
            $grid->column('status')->label([
                'Selesai' => 'success',
                'Proses' => 'primary',
            ]);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

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
        return Show::make($id, new Project(), function (Show $show) {
            $show->field('id');
            $show->field('nama_klien');
            $show->field('nama_project');
            $show->field('deskripsi');
            $show->field('harga');
            $show->field('tanggal_mulai');
            $show->field('tanggal_selesai');
            $show->field('status');
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
        return Form::make(new Project(), function (Form $form) {
            $form->display('id');
            $form->text('nama_klien');
            $form->text('nama_project');
            $form->text('deskripsi');
            $form->currency('harga', 'Harga')
                ->symbol('Rp')
                ->required();
            $form->date('tanggal_mulai');
            $form->date('tanggal_selesai');
            $form->radio('status', 'Status')
                ->options([
                    'proses' => 'Proses',
                    'selesai' => 'Selesai',
                ])->default('proses')->required();

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
