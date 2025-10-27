<?php

namespace App\Admin\Controllers;

use App\Models\CicilanHutang;
use App\Models\Hutang;
use App\Models\Kas;
use App\Models\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;

class CicilanHutangController extends AdminController
{
    protected function grid()
    {
        return Grid::make(CicilanHutang::with('hutang.tim'), function (Grid $grid) {
            //$grid->column('id')->sortable();
            $grid->column('hutang.tim.nama', 'Nama Karyawan');
            $grid->column('nominal_cicilan', 'Nominal Cicilan')->display(fn($v) => 'Rp ' . number_format($v, 0, ',', '.'));
            $grid->column('tanggal_bayar', 'Tanggal Bayar');
            $grid->column('keterangan');

            $grid->disableDeleteButton();   // hilangkan tombol delete di setiap baris
            $grid->disableBatchDelete();   // hilangkan hapus massal juga

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->like('hutang.tim.nama', 'Nama Karyawan'); // filter berdasarkan nama relasi
                $filter->between('tanggal_bayar', 'Tanggal Bayar')->date();
            });
        });
    }

    protected function form()
    {
        return Form::make(new CicilanHutang(), function (Form $form) {
            $form->display('id');

            $form->select('id_hutang', 'Pilih Hutang')->options(
                Hutang::with('tim')
                    ->where('sisa_hutang', '>', 0) // hanya tampilkan hutang yang belum lunas
                    ->get()
                    ->mapWithKeys(function ($item) {
                        return [
                            $item->id => $item->tim->nama . ' - Sisa Hutang: Rp' . number_format($item->sisa_hutang, 0, ',', '.')
                        ];
                    })
            )
                ->required();

            $form->number('nominal_cicilan', 'Nominal Cicilan')->required();
            $form->date('tanggal_bayar', 'Tanggal Bayar')->default(now());
            $form->textarea('keterangan');

            $form->saving(function (Form $form) {
                $hutang = Hutang::with('tim')->find($form->id_hutang);
                if (!$hutang) {
                    return $form->response()->error('Data hutang tidak ditemukan.');
                }

                $tim = $hutang->tim;
                if (!$tim) {
                    return $form->response()->error('Data karyawan tidak ditemukan.');
                }

                // ✅ Ambil nilai cicilan lama (jika sedang edit)
                $oldCicilan = $form->model()->exists ? $form->model()->nominal_cicilan : 0;
                $selisih = $form->nominal_cicilan - $oldCicilan;

                // Jika cicilan naik, pastikan tidak melebihi sisa hutang
                if ($selisih > 0 && $selisih > $hutang->sisa_hutang) {
                    return $form->response()->error('Nominal cicilan baru melebihi sisa hutang.');
                }

                // 🔄 Update nilai sisa hutang
                $hutang->sisa_hutang -= $selisih; // bisa positif atau negatif (naik/turun)
                if ($hutang->sisa_hutang <= 0) {
                    $hutang->sisa_hutang = 0;
                    $hutang->status = 'Lunas';
                } elseif ($hutang->status === 'Lunas' && $hutang->sisa_hutang > 0) {
                    $hutang->status = 'Belum Lunas';
                }
                $hutang->save();

                // 🔹 Update gaji & potongan cicilan
                $tim->gaji = max(0, ($tim->gaji ?? 0) - $selisih);
                $tim->total_potongan_cicilan = ($tim->total_potongan_cicilan ?? 0) + $selisih;
                $tim->save();

                // 🔸 Catat perubahan di Kas hanya jika ada selisih
                if ($selisih != 0) {
                    Kas::create([
                        'tanggal' => $form->tanggal_bayar ?? now(),
                        'jumlah' => $selisih,
                        'cash' => 0,
                        'keterangan' => 'Perubahan cicilan hutang oleh ' . $tim->nama .
                            ' sebesar Rp ' . number_format($selisih, 0, ',', '.') .
                            ' (Edit Cicilan)',
                    ]);

                    // Update saldo kas terakhir
                    $lastKas = Kas::orderByDesc('id')->first();
                    if ($lastKas) {
                        $lastKas->saldo_akhir = ($lastKas->saldo_akhir ?? 0) + $selisih;
                        $lastKas->save();
                    }
                }
            });
        });
    }
}
