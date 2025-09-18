<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Gaji;
use App\Models\Tim;
use App\Models\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class GajiController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Gaji(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('id_tim', 'Karyawan')
                ->display(function ($id) {
                    return Tim::find($id)?->nama ?? '-';
                });
            $grid->column('id_project', 'Project')
                ->display(function ($id) {
                    return \App\Models\Project::find($id)?->nama_project ?? '-';
                });
            $grid->column('jumlah', 'Jumlah')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $grid->column('tanggal')->sortable();
            $grid->column('metode_bayar');
            // $grid->column('created_at');
            // $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id_tim', 'Karyawan')->select(Tim::pluck('nama', 'id'));
                $filter->equal('id_project', 'Project')->select(Project::pluck('nama_project', 'id'));

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
        return Show::make($id, new Gaji(), function (Show $show) {
            $show->field('id');
            $show->field('id_tim', 'Karyawan')->as(function ($id) {
                return Tim::find($id)?->nama ?? '-';
            });
             $show->field('id_project', 'Project')->as(function ($id) {
                return Project::find($id)?->nama_project ?? '-';
            });
            $show->field('jumlah')->as(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $show->field('tanggal');
            $show->field('metode_bayar');
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
        return Form::make(new Gaji(), function (Form $form) {
            $form->display('id');

            $form->select('id_tim')
            ->options(Tim::pluck('nama', 'id'))
            ->required();

            $form->select('id_project')
            ->options(\App\Models\Project::pluck('nama_project', 'id'))
            ->required();

            $form->currency('jumlah', 'Jumlah')
            ->symbol('Rp')
            ->saving(function ($value) {
                //langsung angka penuh
                return (int) str_replace(',', '', $value);
            });

            $form->date('tanggal')->default(now());
            $form->select('metode_bayar')->options([
            'Transfer' => 'Transfer',
            'Cash' => 'Cash',
            ])->default('Transfer');


            $form->display('created_at');
            $form->display('updated_at');

           $form->saved(function (Form $form, $result) {
                $tim = \App\Models\Tim::find($form->id_tim);
                if ($tim) {
                    $total = \App\Models\Gaji::where('id_tim', $tim->id)->sum('jumlah');
                    $tim->gaji = $total;
                    $tim->save();
                }
            });


        });
    }
}
