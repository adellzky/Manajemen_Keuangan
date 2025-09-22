<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Mitra;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class MitraController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Mitra(), function (Grid $grid) {
            $grid->column('instansi', 'Instansi');
            $grid->column('nama', 'Nama');
            $grid->column('alamat', 'Alamat');
            $grid->column('email', 'Email');
            $grid->column('telepon', 'Telepon');
            $grid->column('rekening', 'Bank');
            $grid->column('norek', 'No. Rekening');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id', 'ID');
                $filter->like('instansi', 'Instansi');
                $filter->like('nama', 'Nama');
                $filter->like('email', 'Email');
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
        return Show::make($id, new Mitra(), function (Show $show) {
            $show->field('id');
            $show->field('instansi');
            $show->field('nama');
            $show->field('alamat');
            $show->field('email');
            $show->field('telepon');
            $show->field('rekening');
            $show->field('norek');
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
        return Form::make(new Mitra(), function (Form $form) {
            $form->display('id');
            $form->text('instansi')->required();
            $form->text('nama')->required();
            $form->textarea('alamat');
            $form->email('email');
            $form->text('telepon');
            $form->text('rekening')->placeholder('Nama Bank / Rekening');
            $form->text('norek')->placeholder('Nomor Rekening');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
