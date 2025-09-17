<?php

namespace Database\Seeders;

use Dcat\Admin\Models;
use Illuminate\Database\Seeder;
use DB;

class AdminTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // base tables
        Models\Menu::truncate();
        Models\Menu::insert(
            [
                [
                    "id" => 2,
                    "parent_id" => 0,
                    "order" => 8,
                    "title" => "Admin",
                    "icon" => "feather icon-settings",
                    "uri" => "",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 3,
                    "parent_id" => 2,
                    "order" => 9,
                    "title" => "Users",
                    "icon" => "",
                    "uri" => "auth/users",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 4,
                    "parent_id" => 2,
                    "order" => 10,
                    "title" => "Roles",
                    "icon" => "",
                    "uri" => "auth/roles",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 5,
                    "parent_id" => 2,
                    "order" => 11,
                    "title" => "Permission",
                    "icon" => "",
                    "uri" => "auth/permissions",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 6,
                    "parent_id" => 2,
                    "order" => 12,
                    "title" => "Menu",
                    "icon" => "",
                    "uri" => "auth/menu",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 7,
                    "parent_id" => 2,
                    "order" => 13,
                    "title" => "Extensions",
                    "icon" => "",
                    "uri" => "auth/extensions",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 9,
                    "parent_id" => 0,
                    "order" => 2,
                    "title" => "Pendapatan",
                    "icon" => "fa-download",
                    "uri" => "/pendapatan",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:24:22",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 10,
                    "parent_id" => 0,
                    "order" => 4,
                    "title" => "Kas Perusahaan",
                    "icon" => "fa-building",
                    "uri" => "/kas",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:24:57",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 11,
                    "parent_id" => 0,
                    "order" => 3,
                    "title" => "Pengeluaran",
                    "icon" => "fa-upload",
                    "uri" => "/pengeluaran",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:27:40",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 12,
                    "parent_id" => 0,
                    "order" => 6,
                    "title" => "Gaji",
                    "icon" => "fa-cc",
                    "uri" => "/gaji",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:28:21",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 13,
                    "parent_id" => 0,
                    "order" => 5,
                    "title" => "Tim",
                    "icon" => "fa-users",
                    "uri" => "/tim",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:28:44",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 14,
                    "parent_id" => 0,
                    "order" => 1,
                    "title" => "Project",
                    "icon" => "fa-cubes",
                    "uri" => "/project",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:29:39",
                    "updated_at" => "2025-09-16 04:34:56"
                ],
                [
                    "id" => 15,
                    "parent_id" => 0,
                    "order" => 7,
                    "title" => "Rekap Kuangan",
                    "icon" => "fa-dollar",
                    "uri" => "/rekap-keuangan",
                    "extension" => "",
                    "show" => 1,
                    "created_at" => "2025-09-16 04:34:07",
                    "updated_at" => "2025-09-16 04:34:56"
                ]
            ]
        );

        Models\Permission::truncate();
        Models\Permission::insert(
            [
                [
                    "id" => 1,
                    "name" => "Auth management",
                    "slug" => "auth-management",
                    "http_method" => "",
                    "http_path" => "",
                    "order" => 1,
                    "parent_id" => 0,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => NULL
                ],
                [
                    "id" => 2,
                    "name" => "Users",
                    "slug" => "users",
                    "http_method" => "",
                    "http_path" => "/auth/users*",
                    "order" => 2,
                    "parent_id" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => NULL
                ],
                [
                    "id" => 3,
                    "name" => "Roles",
                    "slug" => "roles",
                    "http_method" => "",
                    "http_path" => "/auth/roles*",
                    "order" => 3,
                    "parent_id" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => NULL
                ],
                [
                    "id" => 4,
                    "name" => "Permissions",
                    "slug" => "permissions",
                    "http_method" => "",
                    "http_path" => "/auth/permissions*",
                    "order" => 4,
                    "parent_id" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => NULL
                ],
                [
                    "id" => 5,
                    "name" => "Menu",
                    "slug" => "menu",
                    "http_method" => "",
                    "http_path" => "/auth/menu*",
                    "order" => 5,
                    "parent_id" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => NULL
                ],
                [
                    "id" => 6,
                    "name" => "Extension",
                    "slug" => "extension",
                    "http_method" => "",
                    "http_path" => "/auth/extensions*",
                    "order" => 6,
                    "parent_id" => 1,
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => NULL
                ]
            ]
        );

        Models\Role::truncate();
        Models\Role::insert(
            [
                [
                    "id" => 1,
                    "name" => "Administrator",
                    "slug" => "administrator",
                    "created_at" => "2025-09-16 04:03:32",
                    "updated_at" => "2025-09-16 04:03:32"
                ]
            ]
        );

        Models\Setting::truncate();
		Models\Setting::insert(
			[

            ]
		);

		Models\Extension::truncate();
		Models\Extension::insert(
			[

            ]
		);

		Models\ExtensionHistory::truncate();
		Models\ExtensionHistory::insert(
			[

            ]
		);

        // pivot tables
        DB::table('admin_permission_menu')->truncate();
		DB::table('admin_permission_menu')->insert(
			[

            ]
		);

        DB::table('admin_role_menu')->truncate();
        DB::table('admin_role_menu')->insert(
            [
                [
                    "role_id" => 1,
                    "menu_id" => 9,
                    "created_at" => "2025-09-16 04:24:22",
                    "updated_at" => "2025-09-16 04:24:22"
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 10,
                    "created_at" => "2025-09-16 04:24:57",
                    "updated_at" => "2025-09-16 04:24:57"
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 12,
                    "created_at" => "2025-09-16 04:28:21",
                    "updated_at" => "2025-09-16 04:28:21"
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 13,
                    "created_at" => "2025-09-16 04:28:44",
                    "updated_at" => "2025-09-16 04:28:44"
                ],
                [
                    "role_id" => 1,
                    "menu_id" => 14,
                    "created_at" => "2025-09-16 04:29:39",
                    "updated_at" => "2025-09-16 04:29:39"
                ]
            ]
        );

        DB::table('admin_role_permissions')->truncate();
        DB::table('admin_role_permissions')->insert(
            [

            ]
        );

        // finish
    }
}
