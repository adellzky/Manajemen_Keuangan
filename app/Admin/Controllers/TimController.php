<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Tim;
use App\Models\Gaji;
use App\Models\Project;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Barryvdh\DomPDF\Facade\Pdf;
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
                $actions->append("<a href='".url("admin/tim/$id/slip")."' target='_blank' class='btn btn-sm btn-primary'>
                    <i class='feather icon-file-text'></i> Slip Gaji
                </a>");

               $actions->append("<a href='".url("admin/tim/$id/ambil-gaji")."' class='btn btn-sm btn-warning'>
                    <i class='feather icon-credit-card'></i> Ambil Gaji
                </a>");

            });

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
                $filter->like('nama', 'Nama Karyawan');
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

            $grid->column('tanggal', 'Tanggal')->display(fn($v) =>
                $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-'
            );
            $grid->column('project.nama_project', 'Project');
            $grid->column('jumlah', 'Jumlah')->display(fn($val) =>
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

            $grid->column('tanggal', 'Tanggal')->display(fn($v) =>
                $v ? \Carbon\Carbon::parse($v)->translatedFormat('d F Y') : '-'
            );
            $grid->column('metode_bayar', 'Metode');
            $grid->column('jumlah', 'Nominal Ambil')->display(fn($val) =>
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

    return Form::make(new \App\Models\Gaji(), function (Form $form) use ($tim) {
        $form->hidden('id_tim')->value($tim->id);
        $form->display('nama_karyawan', 'Nama Karyawan')->default($tim->nama);

        $form->currency('jumlah', 'Nominal Ambil')
            ->symbol('Rp')
            ->rules("max:{$tim->gaji}")
            ->required();

        $form->date('tanggal')->default(now());
        $form->select('metode_bayar')->options([
            'Cash' => 'Cash',
            'Transfer' => 'Transfer',
        ])->default('Cash');

        $form->hidden('id_project')->value(null);
    })->action(url("admin/tim/{$tim->id}/ambil-gaji"));

}

public function storeAmbilGaji($id)
{
    $tim = \App\Models\Tim::findOrFail($id);

    $data = request()->all();

    if ($data['jumlah'] > $tim->gaji) {
        return back()->withError("Nominal melebihi total gaji!");
    }

    // Simpan data gaji
    $gaji = new \App\Models\Gaji();
    $gaji->id_tim = $tim->id;
    $gaji->jumlah = $data['jumlah'];
    $gaji->tanggal = $data['tanggal'];
    $gaji->metode_bayar = $data['metode_bayar'];
    $gaji->id_project = null;
    $gaji->save();

    // Update sisa gaji di tim
    $tim->gaji -= $data['jumlah'];
    $tim->save();

    return $this->response()
        ->success("Pengambilan gaji berhasil, sisa gaji Rp " . number_format($tim->gaji, 0, ',', '.'))
        ->redirect('admin/tim');
    }
}
