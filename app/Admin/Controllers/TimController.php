<?php

namespace App\Admin\Controllers;

use App\Models\Gaji;
use App\Models\Project;
use App\Models\Tim;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;
use Dcat\Admin\Http\JsonResponse;
use Illuminate\Http\Response;
use Dcat\Admin\Layout\Content;

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
            $grid->column('nama');
            $grid->column('no_telp');
            $grid->column('atm');
            $grid->column('norek');
            $grid->column('gaji', 'Gaji')->display(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $id = $actions->getKey();

                // tombol slip
                $actions->append("<a href='" . url("admin/tim/$id/slip") . "' target='_blank' class='btn btn-sm btn-primary'>
                    <i class='feather icon-file-text'></i> Slip Gaji
                </a>");

                $actions->append("<a href='" . url("admin/tim/$id/ambil-gaji") . "' class='btn btn-sm btn-warning'>
                    <i class='feather icon-credit-card'></i> Ambil Gaji
                </a>");
            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->equal('id', 'Nama Karyawan')
                    ->select(Tim::pluck('nama', 'id'));
                $filter->between('gaji', 'Range Gaji');
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
            $show->field('nama');
            $show->field('no_telp');
            $show->field('atm');
            $show->field('norek');
            $show->field('gaji')->as(function ($val) {
                return 'Rp ' . number_format($val, 0, ',', '.');
            });
            $show->field('created_at');
            $show->field('updated_at');
            // ✅ Rincian Gaji Per Project
            $show->field('rincian_gaji', '📌 Rincian Gaji Per Project')->unescape()->as(function ($val) use ($show) {
                $grid = new Grid(new Gaji());

                $grid->model()
                    ->where('id_tim', $show->getKey())
                    ->whereNotNull('id_project')
                    ->with('project');

                $grid->column('tanggal', 'Tanggal')->display(
                    fn($v) =>
                    $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-'
                );
                $grid->column('project.nama_project', 'Project');
                $grid->column('jumlah', 'Jumlah')->display(
                    fn($val) =>
                    'Rp ' . number_format($val, 0, ',', '.')
                );

                $grid->disableCreateButton();
                $grid->disableRowSelector();
                $grid->disableActions();
                $grid->disablePagination();

                return $grid->render();
            });

            // ✅ Riwayat Ambil Gaji
            $show->field('riwayat_gaji', '📌 Riwayat Ambil Gaji')->unescape()->as(function ($val) use ($show) {
                $grid = new Grid(new Gaji());

                $grid->model()
                    ->where('id_tim', $show->getKey())
                    ->whereNull('id_project');

                $grid->column('tanggal', 'Tanggal')->display(
                    fn($v) =>
                    $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-'
                );
                $grid->column('metode_bayar', 'Metode');
                $grid->column('jumlah', 'Nominal Ambil')->display(
                    fn($val) =>
                    'Rp ' . number_format($val, 0, ',', '.')
                );

                $grid->disableCreateButton();
                $grid->disableRowSelector();
                $grid->disableActions();
                $grid->disablePagination();

                return $grid->render();
            });
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
            // $form->currency('gaji', 'Gaji')
            //     ->symbol('Rp');


            $form->display('created_at');
            $form->display('updated_at');
        });
    }
    public function slip($id)
    {
        $tim = \App\Models\Tim::with(['gajis.project'])->findOrFail($id);

        // data gaji per bulan
        $gajis = $tim->gajis()->with('project')->get();

        $pdf = Pdf::loadView('pdf.slip-gaji', compact('tim', 'gajis'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream("slip-gaji-{$tim->nama}.pdf");
    }

    public function ambilGaji($id)
    {
        $tim = \App\Models\Tim::findOrFail($id);

        return Content::make(function (Content $content) use ($tim) {
            $content->header("Ambil Gaji - {$tim->nama}");
            $content->description('Form pengambilan gaji');

            $content->body(
                Form::make(new \App\Models\Gaji(), function (Form $form) use ($tim) {
                    $form->hidden('id_tim')->value($tim->id);
                    $form->display('nama_karyawan', 'Nama Karyawan')->default($tim->nama);
                    $form->display('total_gaji', 'Total Gaji Tersedia')->with(function () use ($tim) {
                        return 'Rp ' . number_format($tim->gaji, 0, ',', '.');
                    });

                    $form->number('jumlah', 'Nominal Ambil')
                        ->min(1)
                        ->max($tim->gaji)
                        ->required()
                        ->help('Masukkan nominal tanpa titik/koma, contoh: 3000 (Rp 3.000)');


                    $form->date('tanggal')->default(now());
                    $form->hidden('metode_bayar')->value('Transfer');
                    $form->hidden('id_project')->value(null);
                })->action(url("admin/tim/{$tim->id}/ambil-gaji"))
            );
        });
    }

    public function storeAmbilGaji($id)
    {
        $tim = \App\Models\Tim::findOrFail($id);

        $data = request()->all();

        $data['jumlah'] = preg_replace('/[^\d]/', '', $data['jumlah']);
        $data['jumlah'] = (int) $data['jumlah'];

        if ($data['jumlah'] > $tim->gaji) {
            admin_error('Gagal', 'Nominal gaji melebihi total gaji yang tersedia, gaji tidak bisa diambil.');
            return back();
        }

        // Simpan data gaji
        $gaji = new \App\Models\Gaji();
        $gaji->id_tim = $tim->id;
        $gaji->jumlah = $data['jumlah'];
        $gaji->tanggal = $data['tanggal'];
        $gaji->metode_bayar = $data['metode_bayar'] ?? 'Transfer';
        $gaji->id_project = null;
        $gaji->save();

        $gajiProject = \App\Models\Gaji::where('id_tim', $tim->id)
            ->whereNotNull('id_project')
            ->sum('jumlah');

        $gajiAmbil = \App\Models\Gaji::where('id_tim', $tim->id)
            ->whereNull('id_project')
            ->sum('jumlah');

        $tim->gaji = $gajiProject - $gajiAmbil;
        $tim->save();

        admin_success('Berhasil', "Pengambilan gaji berhasil, sisa gaji Rp " . number_format($tim->gaji, 0, ',', '.'));

        return JsonResponse::make()
            ->redirect(admin_url('tim'));
    }
}
