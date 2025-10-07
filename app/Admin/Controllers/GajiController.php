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
            $grid->model()->whereNotNull('id_project');
            $grid->column('tanggal')->sortable();
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

            $grid->filter(function (Grid\Filter $filter) {
                $filter->panel()->expand(false);
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
            // $show->field('id');
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

        $form->select('id_project', 'Project')
            ->options(Project::pluck('nama_project', 'id'))
            ->required();

        $form->currency('total_gaji', 'Total Gaji')
            ->symbol('Rp')
            ->required();

        $form->table('pembagian', 'Pembagian Gaji', function ($table) {
            $table->select('id_tim', 'Karyawan')
                ->options(Tim::pluck('nama', 'id'))
                ->required();

            $table->currency('jumlah', 'Jumlah')
                ->symbol('Rp');
        })->value(function () use ($form) {
            if ($form->model()->exists) {
                return \App\Models\Gaji::where('id_project', $form->model()->id_project)
                    ->where('tanggal', $form->model()->tanggal)
                    ->get(['id_tim', 'jumlah'])
                    ->toArray();
            }
            return [];
        });


        $form->html('<div id="sisa-gaji" style="font-weight:bold;color:red;margin:10px 0;"></div>');

        \Dcat\Admin\Admin::script(<<<'JS'
        function parseRupiah(val) {
            if (!val) return 0;
            val = val.toString().trim();

            val = val.replace(/[^\d.,]/g, '');

            const hasDot = val.indexOf('.') !== -1;
            const hasComma = val.indexOf(',') !== -1;

            if (hasDot && hasComma) {
                const lastDot = val.lastIndexOf('.');
                const lastComma = val.lastIndexOf(',');
                if (lastDot > lastComma) {
                    val = val.replace(/,/g, '');
                    return parseFloat(val) || 0;
                } else {
                    val = val.replace(/\./g, '').replace(',', '.');
                    return parseFloat(val) || 0;
                }
            }
            if (hasComma && !hasDot) {
                const parts = val.split(',');
                if (parts[1] && parts[1].length === 2) {
                    val = val.replace(/\./g, '').replace(',', '.');
                    return parseFloat(val) || 0;
                } else {
                    val = val.replace(/,/g, '');
                    return parseFloat(val) || 0;
                }
            }

            if (hasDot && !hasComma) {
                const parts = val.split('.');
                if (parts[1] && parts[1].length === 2) {
                    return parseFloat(val) || 0;
                } else {
                    val = val.replace(/\./g, '');
                    return parseFloat(val) || 0;
                }
            }

            return parseFloat(val) || 0;
        }

        function hitungSisaGaji() {
            let total = parseRupiah($('input[name="total_gaji"]').val());
            let sum = 0;
            $('input[name^="pembagian"][name$="[jumlah]"]').each(function(){
                sum += parseRupiah($(this).val());
            });
            let sisa = total - sum;
            $('#sisa-gaji').text('Sisa Gaji: Rp ' + sisa.toLocaleString('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            if (Math.abs(sisa) > 0.0001) {
                $('#sisa-gaji').css('color','red');
            } else {
                $('#sisa-gaji').css('color','green');
            }
        }

        $(document).on('input', 'input[name="total_gaji"], input[name^="pembagian"][name$="[jumlah]"]', function(){
            hitungSisaGaji();
        });

        // jalankan pada load
        $(document).ready(function(){
            hitungSisaGaji();
        });
        JS
                );

                $form->date('tanggal')->default(now());
               $form->radio('metode_bayar', 'Metode Bayar')
                ->options(['Transfer' => 'Transfer'])
                ->default('Transfer');

                $form->display('created_at');
                $form->display('updated_at');

                $form->ignore(['pembagian']);

                $form->saving(function (Form $form) {
    $parseCurrencyToInt = function($val) {
        $s = trim($val ?? '');
        $s = preg_replace('/[^\d\.,]/', '', $s);

        $hasDot = strpos($s, '.') !== false;
        $hasComma = strpos($s, ',') !== false;

        if ($hasDot && $hasComma) {
            if (strrpos($s, '.') > strrpos($s, ',')) {
                $s = str_replace(',', '', $s);
                $float = (float) $s;
            } else {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
                $float = (float) $s;
            }
        } elseif ($hasComma && !$hasDot) {
            $parts = explode(',', $s);
            if (count($parts) > 1 && strlen(end($parts)) === 2) {
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
                $float = (float) $s;
            } else {
                $s = str_replace(',', '', $s);
                $float = (float) $s;
            }
        } elseif ($hasDot && !$hasComma) {
            $parts = explode('.', $s);
            if (count($parts) > 1 && strlen(end($parts)) === 2) {
                $float = (float) $s;
            } else {
                $s = str_replace('.', '', $s);
                $float = (float) $s;
            }
        } else {
            $float = (float) $s;
        }

        return (int) round($float);
    };

    $totalGaji = $parseCurrencyToInt($form->input('total_gaji'));
    $details   = request('pembagian', []);

    $sumPembagian = 0;
    foreach ($details as $row) {
        if (!empty($row['id_tim']) && isset($row['jumlah'])) {
            $sumPembagian += $parseCurrencyToInt($row['jumlah']);
        }
    }

    if ($sumPembagian < $totalGaji) {
        return $form->response()->error(
            'Masih ada sisa Rp ' . number_format($totalGaji - $sumPembagian,0,',','.') . ' yang belum dibagikan!'
        );
    }
    if ($sumPembagian > $totalGaji) {
        return $form->response()->error(
            'Pembagian melebihi total gaji sebesar Rp ' . number_format($sumPembagian - $totalGaji,0,',','.') . '!'
        );
    }

    $projectId   = $form->input('id_project');
    $tanggalBaru = $form->input('tanggal');
    $metode      = $form->input('metode_bayar');

    // cek apakah edit
    if ($form->model()->exists) {
        $tanggalLama = $form->model()->tanggal;

         \App\Models\Gaji::where('id_project', $projectId)
            ->where('tanggal', $tanggalLama)
            ->delete();

    }

    // insert ulang pembagian
    foreach ($details as $row) {
        if (!empty($row['id_tim']) && isset($row['jumlah'])) {
            $amount = $parseCurrencyToInt($row['jumlah']);
            \App\Models\Gaji::create([
                'id_tim'       => $row['id_tim'],
                'id_project'   => $projectId,
                'jumlah'       => $amount,
                'tanggal'      => $tanggalBaru,
                'metode_bayar' => $metode,
            ]);

            // update total gaji karyawan
            $tim = \App\Models\Tim::find($row['id_tim']);
            if ($tim) {
                $gajiProject = \App\Models\Gaji::where('id_tim', $tim->id)
                ->whereNotNull('id_project')
                ->sum('jumlah');

            $gajiAmbil = \App\Models\Gaji::where('id_tim', $tim->id)
                ->whereNull('id_project')
                ->sum('jumlah');

            $tim->gaji += $amount;
            $tim->save();
            }
        }
    }

    return $form->response()
        ->success('Gaji berhasil disimpan!')
        ->redirect('gaji');
});


            });
        }

}
