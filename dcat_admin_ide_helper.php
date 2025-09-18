<?php

/**
 * A helper file for Dcat Admin, to provide autocomplete information to your IDE
 *
 * This file should not be included in your code, only analyzed by your IDE!
 *
 * @author jqh <841324345@qq.com>
 */
namespace Dcat\Admin {
    use Illuminate\Support\Collection;

    /**
     * @property Grid\Column|Collection id
     * @property Grid\Column|Collection name
     * @property Grid\Column|Collection version
     * @property Grid\Column|Collection is_enabled
     * @property Grid\Column|Collection created_at
     * @property Grid\Column|Collection updated_at
     * @property Grid\Column|Collection type
     * @property Grid\Column|Collection detail
     * @property Grid\Column|Collection parent_id
     * @property Grid\Column|Collection order
     * @property Grid\Column|Collection icon
     * @property Grid\Column|Collection uri
     * @property Grid\Column|Collection extension
     * @property Grid\Column|Collection slug
     * @property Grid\Column|Collection http_method
     * @property Grid\Column|Collection http_path
     * @property Grid\Column|Collection permission_id
     * @property Grid\Column|Collection menu_id
     * @property Grid\Column|Collection role_id
     * @property Grid\Column|Collection user_id
     * @property Grid\Column|Collection value
     * @property Grid\Column|Collection username
     * @property Grid\Column|Collection password
     * @property Grid\Column|Collection avatar
     * @property Grid\Column|Collection remember_token
     * @property Grid\Column|Collection uuid
     * @property Grid\Column|Collection connection
     * @property Grid\Column|Collection queue
     * @property Grid\Column|Collection payload
     * @property Grid\Column|Collection exception
     * @property Grid\Column|Collection failed_at
     * @property Grid\Column|Collection id_tim
     * @property Grid\Column|Collection id_project
     * @property Grid\Column|Collection jumlah
     * @property Grid\Column|Collection tanggal
     * @property Grid\Column|Collection metode_bayar
     * @property Grid\Column|Collection saldo_akhir
     * @property Grid\Column|Collection keterangan
     * @property Grid\Column|Collection nama
     * @property Grid\Column|Collection alamat
     * @property Grid\Column|Collection email
     * @property Grid\Column|Collection telepon
     * @property Grid\Column|Collection token
     * @property Grid\Column|Collection sumber
     * @property Grid\Column|Collection tokenable_type
     * @property Grid\Column|Collection tokenable_id
     * @property Grid\Column|Collection abilities
     * @property Grid\Column|Collection last_used_at
     * @property Grid\Column|Collection expires_at
     * @property Grid\Column|Collection nama_klien
     * @property Grid\Column|Collection nama_project
     * @property Grid\Column|Collection deskripsi
     * @property Grid\Column|Collection harga
     * @property Grid\Column|Collection tanggal_mulai
     * @property Grid\Column|Collection tanggal_selesai
     * @property Grid\Column|Collection status
     * @property Grid\Column|Collection status_bayar
     * @property Grid\Column|Collection no_telp
     * @property Grid\Column|Collection atm
     * @property Grid\Column|Collection norek
     * @property Grid\Column|Collection gaji
     * @property Grid\Column|Collection email_verified_at
     *
     * @method Grid\Column|Collection id(string $label = null)
     * @method Grid\Column|Collection name(string $label = null)
     * @method Grid\Column|Collection version(string $label = null)
     * @method Grid\Column|Collection is_enabled(string $label = null)
     * @method Grid\Column|Collection created_at(string $label = null)
     * @method Grid\Column|Collection updated_at(string $label = null)
     * @method Grid\Column|Collection type(string $label = null)
     * @method Grid\Column|Collection detail(string $label = null)
     * @method Grid\Column|Collection parent_id(string $label = null)
     * @method Grid\Column|Collection order(string $label = null)
     * @method Grid\Column|Collection icon(string $label = null)
     * @method Grid\Column|Collection uri(string $label = null)
     * @method Grid\Column|Collection extension(string $label = null)
     * @method Grid\Column|Collection slug(string $label = null)
     * @method Grid\Column|Collection http_method(string $label = null)
     * @method Grid\Column|Collection http_path(string $label = null)
     * @method Grid\Column|Collection permission_id(string $label = null)
     * @method Grid\Column|Collection menu_id(string $label = null)
     * @method Grid\Column|Collection role_id(string $label = null)
     * @method Grid\Column|Collection user_id(string $label = null)
     * @method Grid\Column|Collection value(string $label = null)
     * @method Grid\Column|Collection username(string $label = null)
     * @method Grid\Column|Collection password(string $label = null)
     * @method Grid\Column|Collection avatar(string $label = null)
     * @method Grid\Column|Collection remember_token(string $label = null)
     * @method Grid\Column|Collection uuid(string $label = null)
     * @method Grid\Column|Collection connection(string $label = null)
     * @method Grid\Column|Collection queue(string $label = null)
     * @method Grid\Column|Collection payload(string $label = null)
     * @method Grid\Column|Collection exception(string $label = null)
     * @method Grid\Column|Collection failed_at(string $label = null)
     * @method Grid\Column|Collection id_tim(string $label = null)
     * @method Grid\Column|Collection id_project(string $label = null)
     * @method Grid\Column|Collection jumlah(string $label = null)
     * @method Grid\Column|Collection tanggal(string $label = null)
     * @method Grid\Column|Collection metode_bayar(string $label = null)
     * @method Grid\Column|Collection saldo_akhir(string $label = null)
     * @method Grid\Column|Collection keterangan(string $label = null)
     * @method Grid\Column|Collection nama(string $label = null)
     * @method Grid\Column|Collection alamat(string $label = null)
     * @method Grid\Column|Collection email(string $label = null)
     * @method Grid\Column|Collection telepon(string $label = null)
     * @method Grid\Column|Collection token(string $label = null)
     * @method Grid\Column|Collection sumber(string $label = null)
     * @method Grid\Column|Collection tokenable_type(string $label = null)
     * @method Grid\Column|Collection tokenable_id(string $label = null)
     * @method Grid\Column|Collection abilities(string $label = null)
     * @method Grid\Column|Collection last_used_at(string $label = null)
     * @method Grid\Column|Collection expires_at(string $label = null)
     * @method Grid\Column|Collection nama_klien(string $label = null)
     * @method Grid\Column|Collection nama_project(string $label = null)
     * @method Grid\Column|Collection deskripsi(string $label = null)
     * @method Grid\Column|Collection harga(string $label = null)
     * @method Grid\Column|Collection tanggal_mulai(string $label = null)
     * @method Grid\Column|Collection tanggal_selesai(string $label = null)
     * @method Grid\Column|Collection status(string $label = null)
     * @method Grid\Column|Collection status_bayar(string $label = null)
     * @method Grid\Column|Collection no_telp(string $label = null)
     * @method Grid\Column|Collection atm(string $label = null)
     * @method Grid\Column|Collection norek(string $label = null)
     * @method Grid\Column|Collection gaji(string $label = null)
     * @method Grid\Column|Collection email_verified_at(string $label = null)
     */
    class Grid {}

    class MiniGrid extends Grid {}

    /**
     * @property Show\Field|Collection id
     * @property Show\Field|Collection name
     * @property Show\Field|Collection version
     * @property Show\Field|Collection is_enabled
     * @property Show\Field|Collection created_at
     * @property Show\Field|Collection updated_at
     * @property Show\Field|Collection type
     * @property Show\Field|Collection detail
     * @property Show\Field|Collection parent_id
     * @property Show\Field|Collection order
     * @property Show\Field|Collection icon
     * @property Show\Field|Collection uri
     * @property Show\Field|Collection extension
     * @property Show\Field|Collection slug
     * @property Show\Field|Collection http_method
     * @property Show\Field|Collection http_path
     * @property Show\Field|Collection permission_id
     * @property Show\Field|Collection menu_id
     * @property Show\Field|Collection role_id
     * @property Show\Field|Collection user_id
     * @property Show\Field|Collection value
     * @property Show\Field|Collection username
     * @property Show\Field|Collection password
     * @property Show\Field|Collection avatar
     * @property Show\Field|Collection remember_token
     * @property Show\Field|Collection uuid
     * @property Show\Field|Collection connection
     * @property Show\Field|Collection queue
     * @property Show\Field|Collection payload
     * @property Show\Field|Collection exception
     * @property Show\Field|Collection failed_at
     * @property Show\Field|Collection id_tim
     * @property Show\Field|Collection id_project
     * @property Show\Field|Collection jumlah
     * @property Show\Field|Collection tanggal
     * @property Show\Field|Collection metode_bayar
     * @property Show\Field|Collection saldo_akhir
     * @property Show\Field|Collection keterangan
     * @property Show\Field|Collection nama
     * @property Show\Field|Collection alamat
     * @property Show\Field|Collection email
     * @property Show\Field|Collection telepon
     * @property Show\Field|Collection token
     * @property Show\Field|Collection sumber
     * @property Show\Field|Collection tokenable_type
     * @property Show\Field|Collection tokenable_id
     * @property Show\Field|Collection abilities
     * @property Show\Field|Collection last_used_at
     * @property Show\Field|Collection expires_at
     * @property Show\Field|Collection nama_klien
     * @property Show\Field|Collection nama_project
     * @property Show\Field|Collection deskripsi
     * @property Show\Field|Collection harga
     * @property Show\Field|Collection tanggal_mulai
     * @property Show\Field|Collection tanggal_selesai
     * @property Show\Field|Collection status
     * @property Show\Field|Collection status_bayar
     * @property Show\Field|Collection no_telp
     * @property Show\Field|Collection atm
     * @property Show\Field|Collection norek
     * @property Show\Field|Collection gaji
     * @property Show\Field|Collection email_verified_at
     *
     * @method Show\Field|Collection id(string $label = null)
     * @method Show\Field|Collection name(string $label = null)
     * @method Show\Field|Collection version(string $label = null)
     * @method Show\Field|Collection is_enabled(string $label = null)
     * @method Show\Field|Collection created_at(string $label = null)
     * @method Show\Field|Collection updated_at(string $label = null)
     * @method Show\Field|Collection type(string $label = null)
     * @method Show\Field|Collection detail(string $label = null)
     * @method Show\Field|Collection parent_id(string $label = null)
     * @method Show\Field|Collection order(string $label = null)
     * @method Show\Field|Collection icon(string $label = null)
     * @method Show\Field|Collection uri(string $label = null)
     * @method Show\Field|Collection extension(string $label = null)
     * @method Show\Field|Collection slug(string $label = null)
     * @method Show\Field|Collection http_method(string $label = null)
     * @method Show\Field|Collection http_path(string $label = null)
     * @method Show\Field|Collection permission_id(string $label = null)
     * @method Show\Field|Collection menu_id(string $label = null)
     * @method Show\Field|Collection role_id(string $label = null)
     * @method Show\Field|Collection user_id(string $label = null)
     * @method Show\Field|Collection value(string $label = null)
     * @method Show\Field|Collection username(string $label = null)
     * @method Show\Field|Collection password(string $label = null)
     * @method Show\Field|Collection avatar(string $label = null)
     * @method Show\Field|Collection remember_token(string $label = null)
     * @method Show\Field|Collection uuid(string $label = null)
     * @method Show\Field|Collection connection(string $label = null)
     * @method Show\Field|Collection queue(string $label = null)
     * @method Show\Field|Collection payload(string $label = null)
     * @method Show\Field|Collection exception(string $label = null)
     * @method Show\Field|Collection failed_at(string $label = null)
     * @method Show\Field|Collection id_tim(string $label = null)
     * @method Show\Field|Collection id_project(string $label = null)
     * @method Show\Field|Collection jumlah(string $label = null)
     * @method Show\Field|Collection tanggal(string $label = null)
     * @method Show\Field|Collection metode_bayar(string $label = null)
     * @method Show\Field|Collection saldo_akhir(string $label = null)
     * @method Show\Field|Collection keterangan(string $label = null)
     * @method Show\Field|Collection nama(string $label = null)
     * @method Show\Field|Collection alamat(string $label = null)
     * @method Show\Field|Collection email(string $label = null)
     * @method Show\Field|Collection telepon(string $label = null)
     * @method Show\Field|Collection token(string $label = null)
     * @method Show\Field|Collection sumber(string $label = null)
     * @method Show\Field|Collection tokenable_type(string $label = null)
     * @method Show\Field|Collection tokenable_id(string $label = null)
     * @method Show\Field|Collection abilities(string $label = null)
     * @method Show\Field|Collection last_used_at(string $label = null)
     * @method Show\Field|Collection expires_at(string $label = null)
     * @method Show\Field|Collection nama_klien(string $label = null)
     * @method Show\Field|Collection nama_project(string $label = null)
     * @method Show\Field|Collection deskripsi(string $label = null)
     * @method Show\Field|Collection harga(string $label = null)
     * @method Show\Field|Collection tanggal_mulai(string $label = null)
     * @method Show\Field|Collection tanggal_selesai(string $label = null)
     * @method Show\Field|Collection status(string $label = null)
     * @method Show\Field|Collection status_bayar(string $label = null)
     * @method Show\Field|Collection no_telp(string $label = null)
     * @method Show\Field|Collection atm(string $label = null)
     * @method Show\Field|Collection norek(string $label = null)
     * @method Show\Field|Collection gaji(string $label = null)
     * @method Show\Field|Collection email_verified_at(string $label = null)
     */
    class Show {}

    /**
     
     */
    class Form {}

}

namespace Dcat\Admin\Grid {
    /**
     
     */
    class Column {}

    /**
     
     */
    class Filter {}
}

namespace Dcat\Admin\Show {
    /**
     
     */
    class Field {}
}
