<?php

namespace App\Admin\Controllers;

use App\Models\Hutang;
use App\Models\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class HutangController extends AdminController
{
    protected function grid()
    {
        return Grid::make(Hutang::with('tim'), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('tim.nama', 'Nama Karyawan');
            $grid->column('jumlah_hutang', 'Jumlah Hutang');
            $grid->column('sisa_hutang', 'Sisa Hutang');
            $grid->column('status')->label([
                'Belum Lunas' => 'danger',
                'Lunas' => 'success',
            ]);
            $grid->column('tanggal_pinjam');
            $grid->column('keterangan');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->like('tim.nama', 'Nama Karyawan');
                $filter->equal('status', 'Status Hutang')->select([
                    'Belum Lunas' => 'Belum Lunas',
                    'Lunas' => 'Lunas',
                ]);
            });
        });
    }

    protected function form()
    {
        return Form::make(new Hutang(), function (Form $form) {
            $form->display('id');
            $form->select('id_tim', 'Nama Karyawan')->options(Tim::pluck('nama', 'id'))->required();
            $form->number('jumlah_hutang', 'Jumlah Hutang')->required();
            $form->date('tanggal_pinjam', 'Tanggal Pinjam')->default(now());
            $form->textarea('keterangan');

            $form->hidden('sisa_hutang');
            $form->hidden('status')->default('Belum Lunas');

            // Otomatis set nilai sisa_hutang dan status sebelum disimpan
            $form->saving(function (Form $form) {
                $tim = Tim::find($form->id_tim);

                if ($tim) {
                    // Jika karyawan sudah punya hutang belum lunas
                    $existing = Hutang::where('id_tim', $tim->id)
                        ->where('status', 'Belum Lunas')
                        ->first();

                    if ($existing) {
                        // Tambahkan nominal ke hutang lama
                        $existing->jumlah_hutang += $form->jumlah_hutang;
                        $existing->sisa_hutang += $form->jumlah_hutang;
                        $existing->save();

                        // Batalkan pembuatan record baru
                        return $form->response()
                            ->success('Hutang berhasil ditambahkan ke hutang lama.')
                            ->redirect('hutang');
                    } else {
                        // Buat hutang baru seperti biasa
                        $form->sisa_hutang = $form->jumlah_hutang;
                        $form->status = 'Belum Lunas';
                    }
                }
            });
        });
    }
}
