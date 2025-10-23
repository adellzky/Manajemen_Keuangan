<?php

namespace App\Admin\Controllers;

use App\Models\Hutang;
use App\Models\Kas;
use App\Models\Pengeluaran;
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
           // $grid->column('id')->sortable();
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

            $form->saving(function (Form $form) {
                $tim = Tim::find($form->id_tim);

                if (!$tim) return;

                // 🔹 Jika ini adalah update (edit data lama)
                if ($form->model()->exists) {
                    $hutang = Hutang::find($form->model()->id);

                    if ($hutang) {
                        // Hitung selisih antara nilai lama dan nilai baru
                        $selisih = $form->jumlah_hutang - $hutang->jumlah_hutang;

                        // Update nilai hutang dan sisa hutang
                        $hutang->jumlah_hutang = $form->jumlah_hutang;
                        $hutang->sisa_hutang = max(0, $hutang->sisa_hutang + $selisih);
                        $hutang->tanggal_pinjam = $form->tanggal_pinjam;
                        $hutang->keterangan = $form->keterangan;
                        $hutang->save();

                        // Catat perubahan di pengeluaran hanya jika nilai bertambah
                        if ($selisih > 0) {
                            Pengeluaran::create([
                                'tanggal' => $form->tanggal_pinjam ?? now(),
                                'jumlah' => $selisih,
                                'sumber_dana' => 'bank',
                                'keterangan' => 'Penyesuaian hutang (' . $tim->nama . ') naik sebesar Rp ' . number_format($selisih, 0, ',', '.'),
                            ]);
                        }

                        return $form->response()
                            ->success('Data hutang berhasil diperbarui.')
                            ->redirect('hutang');
                    }
                }

                // 🔹 Jika ini adalah data baru (create)
                $existing = Hutang::where('id_tim', $tim->id)
                    ->where('status', 'Belum Lunas')
                    ->whereDate('tanggal_pinjam', $form->tanggal_pinjam)
                    ->first();

                if ($existing) {
                    // Kalau tanggal sama dan status belum lunas, tambahkan ke hutang itu
                    $existing->jumlah_hutang += $form->jumlah_hutang;
                    $existing->sisa_hutang += $form->jumlah_hutang;
                    $existing->save();

                    Kas::create([
                        'tanggal' => $form->tanggal_pinjam ?? now(),
                        'jumlah' => 0,
                        'cash' => 0,
                        'keterangan' => 'Penambahan hutang (' . $tim->nama . ') sebesar Rp ' . number_format($form->jumlah_hutang, 0, ',', '.'),
                    ]);

                    Pengeluaran::create([
                        'tanggal' => $form->tanggal_pinjam ?? now(),
                        'jumlah' => $form->jumlah_hutang,
                        'sumber_dana' => 'bank',
                        'keterangan' => 'Pinjaman kepada ' . $tim->nama,
                    ]);

                    return $form->response()
                        ->success('Hutang ditambahkan ke tanggal yang sama dan saldo bank dikurangi otomatis.')
                        ->redirect('hutang');
                } else {
                    // Kalau tanggal berbeda, buat data baru
                    $form->sisa_hutang = $form->jumlah_hutang;
                    $form->status = 'Belum Lunas';

                    Pengeluaran::create([
                        'tanggal' => $form->tanggal_pinjam ?? now(),
                        'jumlah' => $form->jumlah_hutang,
                        'sumber_dana' => 'bank',
                        'keterangan' => 'Pinjaman kepada ' . $tim->nama,
                    ]);
                }
            });
        });
    }
}
