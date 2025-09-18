<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->column('id')->sortable();
            $grid->column('nama');
            $grid->column('no_telp');
            $grid->column('atm');
            $grid->column('norek');
             $grid->column('gaji', 'Gaji')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
                $filter->equal('nama');

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
            $show->field('id');
            $show->field('nama');
            $show->field('no_telp');
            $show->field('atm');
            $show->field('norek');
            $show->field('gaji')->as(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
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
        return Form::make(new Tim(), function (Form $form) {
            $form->display('id');
            $form->text('nama')->required();
            $form->mobile('no_telp')->required();
            $form->text('atm')->required();
            $form->text('norek')->required();
            $form->currency('gaji', 'Gaji')
                ->symbol('Rp');


            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
