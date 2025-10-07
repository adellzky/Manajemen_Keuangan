<?php

namespace App\Admin\Controllers;

use App\Models\CicilanHutang;
use App\Models\Hutang;
use App\Models\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class CicilanHutangController extends AdminController
{
    protected function grid()
    {
        return Grid::make(CicilanHutang::with('hutang.tim'), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('hutang.tim.nama', 'Nama Karyawan');
            $grid->column('nominal_cicilan', 'Nominal Cicilan')->display(fn($v) => 'Rp ' . number_format($v, 0, ',', '.'));
            $grid->column('tanggal_bayar', 'Tanggal Bayar');
            $grid->column('keterangan');
        });
    }

    protected function form()
    {
        return Form::make(new CicilanHutang(), function (Form $form) {
            $form->display('id');

            // Pilihan hutang dari tabel hutang + nama karyawan
            $form->select('id_hutang', 'Pilih Hutang')->options(
                Hutang::with('tim')->get()->mapWithKeys(function ($item) {
                    return [$item->id => $item->tim->nama . ' - Sisa Hutang: Rp' . number_format($item->sisa_hutang, 0, ',', '.')];
                })
            )->required();

            $form->number('nominal_cicilan', 'Nominal Cicilan')->required();
            $form->date('tanggal_bayar', 'Tanggal Bayar')->default(now());
            $form->textarea('keterangan');

            // 🔧 Update otomatis saat cicilan disimpan
            $form->saving(function (Form $form) {
                $hutang = Hutang::with('tim')->find($form->id_hutang);

                if (!$hutang) {
                    return $form->response()->error('Data hutang tidak ditemukan.');
                }

                // ✅ Cegah nominal lebih besar dari sisa hutang
                if ($form->nominal_cicilan > $hutang->sisa_hutang) {
                    return $form->response()->error('Nominal cicilan tidak boleh lebih besar dari sisa hutang.');
                }


                $hutang->sisa_hutang -= $form->nominal_cicilan;
                if ($hutang->sisa_hutang <= 0) {
                    $hutang->sisa_hutang = 0;
                    $hutang->status = 'Lunas';
                }
                $hutang->save();


                $tim = $hutang->tim;
                if ($tim) {
                    $tim->total_potongan_cicilan = ($tim->total_potongan_cicilan ?? 0) + $form->nominal_cicilan;
                    $tim->gaji -= $form->nominal_cicilan;
                    if ($tim->gaji < 0) {
                        $tim->gaji = 0; // mencegah minus
                    }
                    $tim->save();
                }
            });
        });
    }
}
