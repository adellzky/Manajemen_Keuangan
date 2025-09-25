<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use App\Models\Mitra;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ProjectController extends AdminController
{
    protected function grid()
    {
        return Grid::make(Project::with(['mitra']), function (Grid $grid) {
            $grid->column('mitra.instansi', 'Mitra');
            $grid->column('kategori');
            $grid->column('nama_project');
            $grid->column('deskripsi')->display(function ($val) {
               return \Illuminate\Support\Str::limit($val, 20, '...');
            });
            $grid->column('harga', 'Harga')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $grid->column('tanggal_mulai', 'Mulai');
            $grid->column('tanggal_selesai', 'Selesai');
            $grid->column('status')->label([
                'Selesai' => 'success',
                'Proses' => 'primary',
            ]);
            $grid->column('status_bayar')->label([
                'Belum' => 'danger',
                'Dp'    => 'primary',
                'Lunas' => 'success',
            ]);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id_mitra', 'Mitra')
                    ->select(Mitra::pluck('instansi', 'id'));
                $filter->like('nama_project', 'Nama Project');
                $filter->between('tanggal_selesai', 'Tanggal Selesai')->date();
            });
        });
    }

    protected function detail($id)
    {
        return Show::make($id,Project::with(['mitra']), function (Show $show) {
            $show->field('id');
            $show->field('mitra.instansi', 'Mitra');
            $show->field('kategori');
            $show->field('nama_project');
            $show->field('deskripsi');
            $show->field('harga');
            $show->field('tanggal_mulai');
            $show->field('tanggal_selesai');
            $show->field('status');
            $show->field('status_bayar');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    protected function form()
    {
        return Form::make(new Project(), function (Form $form) {
            $form->display('id');
            $form->select('id_mitra', 'Mitra')
                ->options(Mitra::pluck('instansi', 'id'))
                ->required();
            $form->radio('kategori', 'Kategori')
                ->options([
                    'Jasa'  => 'Jasa',
                    'Produk' => 'Produk',
                ])->default('Proses')->required();
            $form->text('nama_project');
            $form->textarea('deskripsi');
            $form->currency('harga', 'Harga')
                ->symbol('Rp')
                ->required();
            $form->date('tanggal_mulai');
            $form->date('tanggal_selesai');
            $form->radio('status', 'Status')
                ->options([
                    'Proses'  => 'Proses',
                    'Selesai' => 'Selesai',
                ])->default('Proses')->required();

            $form->radio('status_bayar', 'Status Bayar')
                ->options([
                    'Belum' => 'Belum',
                    'Dp'    => 'Dp',
                    'Lunas' => 'Lunas',
                ])->default('Belum')->required();

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
