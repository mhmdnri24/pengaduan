<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('ce_opsi')) {
    function ce_opsi($kunci)
    {
        $CI = &get_instance();
        $result = $CI->ce_model->get_opsi($kunci);
        return $result;
    }
}

if (!function_exists('ce_set_opsi')) {
    function ce_set_opsi($kunci, $nilai = '')
    {
        $CI = &get_instance();
        if (is_array($kunci)) {
            foreach ($kunci as $key => $val)
                $result = $CI->ce_model->set_opsi($key, $val);
        } else $result = $CI->ce_model->set_opsi($kunci, $nilai);

        return $result;
    }
}

if (!function_exists('ce_set_msg')) {
    function ce_set_msg($item, $value)
    {
        $CI = &get_instance();
        $CI->session->set_tempdata($item, $value, 3);
    }
}

if (!function_exists('ce_msg')) {
    function ce_msg($item, $callout = false)
    {
        $result = null;
        $CI = &get_instance();
        if ($CI->session->tempdata($item)) {
            if ($callout == false) {
                $result = '<div class="alert alert-' . $item . ' alert-dismissible">
                      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                      ' . $CI->session->tempdata($item) . '
                </div>';
            } else {
                $result = '<div class="callout callout-' . $item . '">
                      ' . $CI->session->tempdata($item) . '
                </div>';
            }
            $CI->session->unset_tempdata($item);
        }
        return $result;
    }
}

if (!function_exists('ce_date')) {
    function ce_date()
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('ce_num_format')) {
    function ce_num_format($angka)
    {
        return number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('ce_nama_bulan')) {
    function ce_nama_bulan($bulan)
    {
        $nama_bulan = array(
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        );

        return isset($nama_bulan[$bulan]) ? $nama_bulan[$bulan] : '';
    }
}

if (!function_exists('ce_ikon_boolean')) {
    function ce_ikon_boolean($int)
    {
        if ($int == 1)
            return '<i class="fa fa-check"></i>';
        else
            return '<i class="fa fa-close"></i>';
    }
}

if (!function_exists('ce_get_hak_akses')) {
    function ce_get_hak_akses($id_level)
    {
        $CI = &get_instance();
        $level = $CI->level_m->level_by_id($id_level);
        
        if (empty($level) || empty($level->hak_akses) || $level->hak_akses === 'null') {
            return [];
        }
        
        $result = json_decode($level->hak_akses, true);
        return is_array($result) ? $result : [];
    }
}

if (!function_exists('ce_hak_akses')) {
    function ce_hak_akses($user_akses, $error_page = true)
    {
        $CI = &get_instance();
        $hak_akses = ce_get_hak_akses($CI->session->id_level);

        if (!@in_array($user_akses, $hak_akses) && $CI->session->id_level != 1) {
            return false;
        }

        return true;
    }
}

if (!function_exists('ce_cek_hak_akses')) {
    function ce_cek_hak_akses($user_akses)
    {
        $CI = &get_instance();
        $hak_akses = ce_get_hak_akses($CI->session->id_level);

        if (!@in_array($user_akses, $hak_akses) && $CI->session->id_level != 1) {
            return false;
        }

        return true;
    }
}

if (!function_exists('ce_count_duplicate_array')) {
    function ce_count_duplicate_array($array1, $array2)
    {
        $result = 0;
        foreach ($array1 as $key => $val) {
            if (@in_array($val, $array2))
                $result++;
        }

        return $result;
    }
}

if (!function_exists('ce_anchor')) {
    function ce_anchor($user_akses, $uri = '', $title = '', $attributes = '')
    {
        $CI = &get_instance();
        $hak_akses = ce_get_hak_akses($CI->session->userdata('id_level'));

        if (@in_array($user_akses, $hak_akses) || $CI->session->userdata('id_level') == 1)
            return anchor($uri, $title, $attributes);
    }
}

if (!function_exists('ce_button')) {
    function ce_button($user_akses, $type = 'button', $label = '', $attributes = '')
    {
        $CI = &get_instance();
        $hak_akses = ce_get_hak_akses($CI->session->userdata('id_level'));

        if (@in_array($user_akses, $hak_akses) || $CI->session->userdata('id_level') == 1) {
            switch ($type) {
                default:
                    return '<button type="' . $type . '" ' . $attributes . '>' . $label . '</button>';
                    break;
                case 'href':
                    return '<a href="javascript:;" ' . $attributes . '>' . $label . '</a>';
                    break;
            }
        }
    }
}

if (!function_exists('ce_active_menu')) {
    function ce_active_menu($access_code)
    {
        $CI = &get_instance();
        $CI->config->set_item('active_menu', $access_code);
    }
}

if (!function_exists('ce_nav_menu')) {
    function ce_nav_menu($menu)
    {
        $CI = &get_instance();
        $result = '';
        
        // Get current URL for better menu matching
        $current_url = $CI->router->fetch_directory() . $CI->router->fetch_class() . '/' . $CI->router->fetch_method();
        $current_url = rtrim($current_url, '/');
        
        foreach ($menu as $row) {
            $menu_data = ce_normalize_menu_format($row);
            
            if (!empty($menu_data['submenu'])) {
                // Cek hak akses parent menu
                if (!empty($menu_data['hak_akses'])) {
                    $has_access = ce_cek_hak_akses($menu_data['hak_akses']) || $CI->session->userdata('id_level') == 1;
                } else {
                    $has_access = true;
                }
                
                if ($has_access) {
                    // Cek apakah salah satu submenu aktif
                    $is_parent_active = false;
                    foreach ($menu_data['submenu'] as $child) {
                        $child_data = ce_normalize_menu_format($child);
                        
                        // Check by URL
                        if (!empty($child_data['url'])) {
                            $child_url = trim($child_data['url'], '/');
                            if ($current_url == $child_url || strpos($current_url, $child_url) === 0) {
                                $is_parent_active = true;
                                break;
                            }
                        }
                        
                        // Check by active_menu config
                        if (!empty($child_data['hak_akses']) && $child_data['hak_akses'] == $CI->config->item('active_menu')) {
                            $is_parent_active = true;
                            break;
                        }
                    }
                    
                    $active_class = $is_parent_active ? 'treeview active menu-open' : 'treeview';
                    $result .= '<li class="' . $active_class . '">
                        <a href="#">
                            <i class="' . $menu_data['icon'] . '"></i> <span>' . $menu_data['text'] . '</span>
                            <span class="pull-right-container">
                              <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">' . ce_nav_menu($menu_data['submenu']) . '</ul>
                    </li>';
                }
            } else {
                // Menu item tunggal
                $item_url = !empty($menu_data['url']) ? trim($menu_data['url'], '/') : '';
                $access_code = !empty($menu_data['hak_akses']) ? $menu_data['hak_akses'] : '';
                
                // Cek apakah menu aktif berdasarkan URL atau access_code
                $is_active = false;
                if ($access_code == $CI->config->item('active_menu')) {
                    $is_active = true;
                } elseif ($item_url && ($current_url == $item_url || strpos($current_url, $item_url) === 0)) {
                    $is_active = true;
                }
                
                $active_class = $is_active ? ' class="active"' : '';
                
                // Generate menu link dengan pengecekan hak akses
                if (!empty($menu_data['hak_akses'])) {
                    $menu_link = ce_anchor($menu_data['hak_akses'], $menu_data['url'], '<i class="' . $menu_data['icon'] . '"></i> <span>' . $menu_data['text'] . '</span>');
                } else {
                    $menu_link = '<a href="' . base_url($menu_data['url']) . '"><i class="' . $menu_data['icon'] . '"></i> <span>' . $menu_data['text'] . '</span></a>';
                }
                
                $result .= '<li' . $active_class . '>' . $menu_link . '</li>';
            }
        }

        return $result;
    }
}

if (!function_exists('format_rupiah')) {
    function format_rupiah($angka) {
        if (!is_numeric($angka)) {
            return $angka;
        }
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

if (!function_exists('json_output')) {
    function json_output($data) {
        $CI =& get_instance();
        $CI->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}

if (!function_exists('debug_log')) {
    function debug_log($data, $prefix = 'DEBUG', $to_file = true) {
        $CI =& get_instance();
        
        // Format data untuk log
        $log_message = '[' . $prefix . '] ' . date('Y-m-d H:i:s') . ' - ';
        
        if (is_array($data) || is_object($data)) {
            $log_message .= print_r($data, true);
        } else {
            $log_message .= $data;
        }
        
        // Tulis ke log file jika diperlukan
        if ($to_file) {
            $CI->load->helper('file');
            $log_file = APPPATH . 'logs/debug_log_' . date('Y-m-d') . '.log';
            
            // Buat file jika tidak ada
            if (!file_exists($log_file)) {
                write_file($log_file, "=== DEBUG LOG STARTED ===\n");
            }
            
            // Tambahkan log ke file
            write_file($log_file, $log_message . "\n", 'a+');
        }
        
        // Selalu tampilkan di log CI
        log_message('debug', $log_message);
    }
}

if (!function_exists('ce_get_dynamic_menu')) {
    function ce_get_dynamic_menu()
    {
        $CI = &get_instance();
        
        // Cek apakah model menu_m sudah dimuat
        if (!isset($CI->menu_m)) {
            $CI->load->model('menu/menu_m');
        }
        
        // Cek apakah tabel master_menu ada
        if (!$CI->db->table_exists('master_menu')) {
            return [];
        }
        
        return $CI->menu_m->get_dynamic_menu();
    }
}


// if (!function_exists('ce_get_dynamic_hak_akses')) {
//     function ce_get_dynamic_hak_akses()
//     {
//         $CI = &get_instance();

//         // Load model jika belum dimuat
//         if (!isset($CI->master_hak_akses_m)) {
//             $CI->load->model('master_hak_akses/master_hak_akses_m');
//         }

//         // Cek apakah tabel master_hak_akses ada
//         if (!$CI->db->table_exists('master_hak_akses')) {
//             // Fallback ke config jika tabel belum ada
//             return $CI->config->item('hak_akses');
//         }

//         return $CI->master_hak_akses_m->get_grouped_permissions();
//     }
// }

// if (!function_exists('ce_get_hak_akses_with_fallback')) {
//     function ce_get_hak_akses_with_fallback()
//     {
//         // Coba ambil dari database terlebih dahulu
//         $dynamic_permissions = ce_get_dynamic_hak_akses();

//         if (!empty($dynamic_permissions)) {
//             return $dynamic_permissions;
//         }

//         // Fallback ke config jika database kosong
//         $CI = &get_instance();
//         return $CI->config->item('hak_akses');
//     }
// }

if (!function_exists('ce_add_permission_to_master')) {
    function ce_add_permission_to_master($modul_nama, $permission_key, $permission_label, $description = '')
    {
        $CI = &get_instance();

        // Load model jika belum dimuat
        if (!isset($CI->master_hak_akses_m)) {
            $CI->load->model('master_hak_akses/master_hak_akses_m');
        }

        // Cek apakah tabel master_hak_akses ada
        if (!$CI->db->table_exists('master_hak_akses')) {
            return false;
        }

        // Cek apakah permission sudah ada
        if ($CI->master_hak_akses_m->check_permission_exists($permission_key)) {
            return false; // Sudah ada
        }

        $data = [
            'modul_nama' => $modul_nama,
            'permission_key' => $permission_key,
            'permission_label' => $permission_label,
            'permission_description' => $description,
            'is_active' => 1,
            'sort_order' => $CI->master_hak_akses_m->get_max_sort_order($modul_nama) + 1
        ];

        return $CI->master_hak_akses_m->insert($data);
    }
}

if (!function_exists('ce_register_module_permissions')) {
    function ce_register_module_permissions($modul_nama, $permissions = [])
    {
        $CI = &get_instance();

        // Default permissions untuk setiap module
        $default_permissions = [
            'view' => 'Lihat Data',
            'add' => 'Tambah Data',
            'update' => 'Edit Data',
            'delete' => 'Hapus Data'
        ];

        // Merge dengan permissions yang diberikan
        $permissions = array_merge($default_permissions, $permissions);

        $success_count = 0;
        foreach ($permissions as $action => $label) {
            $permission_key = 'admin.' . strtolower(str_replace(' ', '_', $modul_nama)) . '.' . $action;

            if (ce_add_permission_to_master($modul_nama, $permission_key, $label)) {
                $success_count++;
            }
        }

        return $success_count;
    }
}


if (!function_exists('ce_normalize_menu_format')) {
    function ce_normalize_menu_format($menu_item)
    {
        $normalized = [];
        
        // Handle different menu sources (database vs config)
        if (is_object($menu_item)) {
            $menu_item = (array) $menu_item;
        }
        
        // Map fields from database format
        $normalized['id'] = $menu_item['id'] ?? $menu_item['menu_id'] ?? '';
        $normalized['text'] = $menu_item['text'] ?? $menu_item['label'] ?? $menu_item['menu_label'] ?? '';
        $normalized['url'] = $menu_item['url'] ?? $menu_item['uri'] ?? $menu_item['menu_url'] ?? '';
        $normalized['icon'] = $menu_item['icon'] ?? $menu_item['menu_icon'] ?? 'fa fa-circle-o';
        $normalized['hak_akses'] = $menu_item['hak_akses'] ?? $menu_item['access_code'] ?? $menu_item['menu_access_code'] ?? '';
        
        // Handle submenu
        $submenu = [];
        if (isset($menu_item['child'])) {
            $submenu = $menu_item['child'];
        } elseif (isset($menu_item['sub_menu'])) {
            $submenu = $menu_item['sub_menu'];
        }
        $normalized['submenu'] = $submenu;
        
        return $normalized;
    }
}


if (!function_exists('ce_logout_script')) {
    function ce_logout_script($redirect_url = 'login_mahasiswa/logout')
    {
        return "
        <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Logout?',
                text: 'Anda akan keluar dari sistem',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '" . base_url($redirect_url) . "';
                }
            });
        }
        </script>";
    }
}

if (!function_exists('ce_get_kontak_darurat')) {
    function ce_get_kontak_darurat()
    {
        $kontak_darurat_opsi = ce_opsi('kontak_darurat');
        $kontak_darurat = !empty($kontak_darurat_opsi) ? json_decode($kontak_darurat_opsi, true) : [];
        return is_array($kontak_darurat) ? $kontak_darurat : [];
    }
}
