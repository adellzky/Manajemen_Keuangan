<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Pendapatan;
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
        return Grid::make(new Pendapatan(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('id_project');
            $grid->column('sumber');
            $grid->column('jumlah');
            $grid->column('tanggal');
            $grid->column('keterangan');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
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
            $form->text('id_project');
            $form->text('sumber');
            $form->text('jumlah');
            $form->text('tanggal');
            $form->text('keterangan');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
