<?php

namespace App\Admin\Controllers;

use App\Models\Project;
use App\Models\Mitra;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Grid\Displayers\Expand;
use Illuminate\Support\Str;
use Dcat\Admin\Http\Controllers\AdminController;

class ProjectController extends AdminController
{
    protected function grid()
    {
        return Grid::make(Project::with(['mitra']), function (Grid $grid) {
            $grid->column('mitra.instansi', 'Mitra');
            $grid->column('kategori');
            $grid->column('nama_project');
            $grid->column('status')->label([
                'Belum'   => 'info',
                'Proses'  => 'primary',
                'Selesai' => 'success',
                'Batal'   => 'danger',
            ]);

            $grid->column('status_bayar')->label([
                'Belum' => 'danger',
                'Dp'    => 'primary',
                'Lunas' => 'success',
            ]);

            // kolom deskripsi, tampil ringkas & expand klik
            $grid->column('Detail')
                ->display(function ($val) {
                    return Str::limit($val, 30, '...');
                })
                ->expand(function (Expand $expand) {
                    // optional: ganti nama tombol
                    $expand->button('');

                    // escape untuk mencegah XSS
                    $deskripsi = e($this->deskripsi);
                    $harga = 'Rp ' . number_format($this->harga ?? 0, 0, ',', '.');
                    $mulai = e($this->tanggal_mulai);
                    $selesai = e($this->tanggal_selesai);

                    return <<<HTML
            <div style="padding:10px;">
            <table class="table table-sm" style="margin-bottom:0">
                <tr><th style="width:120px">Deskripsi</th><td>{$deskripsi}</td></tr>
                <tr><th>Harga</th><td>{$harga}</td></tr>
                <tr><th>Mulai</th><td>{$mulai}</td></tr>
                <tr><th>Selesai</th><td>{$selesai}</td></tr>
            </table>
            </div>
            HTML;
                });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id_mitra', 'Mitra')
                    ->select(Mitra::pluck('instansi', 'id'));
                $filter->equal('nama_project', 'Nama Project')
                    ->select(Project::pluck('nama_project', 'nama_project'));
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
                    'Belum' => 'Belum',
                    'Proses'  => 'Proses',
                    'Selesai' => 'Selesai',
                    'Batal' => 'Batal',
                ])->default('Belum')->required();

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
