<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_kategori extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        // Load model explicitly for HMVC
        $this->load->model('master_kategori/master_kategori_m');
        ce_active_menu('admin.master_kategori.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_kategori.view');
        $data['header'] = 'Master <small>Kategori</small>';
        $data['halaman'] = 'master_kategori';
        $data['kategori_parent_list'] = $this->master_kategori_m->kategori_get_parent();
        $data['kategori_tree'] = $this->master_kategori_m->get_full_kategori_tree();
        $data['javascript'] = array(
            'master_kategori/js_master_kategori' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_kategori.view');

        // Filter berdasarkan parent dan status
        $parent_filter = $this->input->post('parent');
        $status_filter = $this->input->post('status');
        $search_value = $this->input->post('search')['value'];

        $result = [];
        $no = 1;
        
        // Get full tree structure
        $tree = $this->master_kategori_m->get_full_kategori_tree();
        
        // Helper function to check if a category or its descendants match the parent filter
        $category_matches_parent_filter = function($kategori, $filter_id) use (&$category_matches_parent_filter) {
            if (empty($filter_id)) return true;
            if ($kategori->id == $filter_id) return true;
            if (isset($kategori->children)) {
                foreach ($kategori->children as $child) {
                    if ($category_matches_parent_filter($child, $filter_id)) return true;
                }
            }
            return false;
        };
        
        // Helper function to check if category matches search
        $category_matches_search = function($kategori, $search_term) use (&$category_matches_search) {
            if (empty($search_term)) return true;
            
            $found = (stripos($kategori->kode_kategori, $search_term) !== false ||
                     stripos($kategori->nama_kategori, $search_term) !== false);
            
            if ($found) return true;
            
            if (isset($kategori->children)) {
                foreach ($kategori->children as $child) {
                    if ($category_matches_search($child, $search_term)) return true;
                }
            }
            return false;
        };
        
        // Helper function to check if category or its descendants match status
        $category_matches_status = function($kategori, $status_filter) use (&$category_matches_status) {
            if (empty($status_filter) && $status_filter !== '0') return true;
            if ($kategori->status == $status_filter) return true;
            
            if (isset($kategori->children)) {
                foreach ($kategori->children as $child) {
                    if ($category_matches_status($child, $status_filter)) return true;
                }
            }
            return false;
        };
        
        foreach ($tree as $root) {
            // Check if this root tree should be included based on filters
            $include_tree = true;
            
            if (!empty($parent_filter)) {
                $include_tree = $category_matches_parent_filter($root, $parent_filter);
            }
            
            if ($include_tree && !empty($search_value)) {
                $include_tree = $category_matches_search($root, $search_value);
            }
            
            if ($include_tree && (!empty($status_filter) || $status_filter === '0')) {
                $include_tree = $category_matches_status($root, $status_filter);
            }
            
            if (!$include_tree) continue;
            
            // Check if root should be displayed
            $display_root = true;
            if (!empty($parent_filter) && $root->id != $parent_filter) {
                // Check if parent filter matches any descendant
                $descendant_matches = false;
                foreach ($root->children as $level1) {
                    if ($level1->id == $parent_filter) {
                        $descendant_matches = true;
                        break;
                    }
                    foreach ($level1->children as $level2) {
                        if ($level2->id == $parent_filter) {
                            $descendant_matches = true;
                            break 2;
                        }
                    }
                }
                if (!$descendant_matches) $display_root = false;
            }
            
            if (!empty($status_filter) && $status_filter !== '' && $root->status != $status_filter) {
                $display_root = false;
            }
            
            if (!empty($search_value)) {
                $root_matches_search = (stripos($root->kode_kategori, $search_value) !== false ||
                                       stripos($root->nama_kategori, $search_value) !== false);
                if (!$root_matches_search) $display_root = false;
            }
            
            // Add root category (Level 0) if it should be displayed
            if ($display_root) {
                $status_badge = $root->status == 1
                    ? '<span class="label label-success">Aktif</span>'
                    : '<span class="label label-danger">Nonaktif</span>';

                $actions = $this->generate_actions($root);
                
                $child_count = count($root->children);
                $total_descendants = $this->count_all_descendants($root->children);
                $kategori_display = '<i class="fa fa-sitemap"></i> ' . $root->nama_kategori;
                if ($total_descendants > 0) {
                    $kategori_display .= ' <small class="text-muted">(' . $total_descendants . ' sub total)</small>';
                }

                $result[] = [
                    'DT_RowClass' => 'parent-row level-0',
                    $no++,
                    $root->kode_kategori,
                    $kategori_display,
                    '<span class="text-muted">Root Kategori</span>',
                    $status_badge,
                    $actions
                ];
            }

            // Add Level 1 children
            foreach ($root->children as $level1) {
                $display_level1 = true;
                
                // Apply filters for level 1
                if (!empty($parent_filter)) {
                    if ($parent_filter == $root->id || $parent_filter == $level1->id) {
                        $display_level1 = true;
                    } else {
                        // Check if level2 children match
                        $level2_matches = false;
                        foreach ($level1->children as $level2) {
                            if ($level2->id == $parent_filter) {
                                $level2_matches = true;
                                break;
                            }
                        }
                        $display_level1 = $level2_matches;
                    }
                }
                
                if (!empty($status_filter) && $status_filter !== '' && $level1->status != $status_filter) {
                    // Check if any level2 children match status
                    $level2_status_match = false;
                    foreach ($level1->children as $level2) {
                        if ($level2->status == $status_filter) {
                            $level2_status_match = true;
                            break;
                        }
                    }
                    if (!$level2_status_match) $display_level1 = false;
                }
                
                if (!empty($search_value)) {
                    $level1_matches_search = (stripos($level1->kode_kategori, $search_value) !== false ||
                                             stripos($level1->nama_kategori, $search_value) !== false);
                    if (!$level1_matches_search) {
                        // Check if any level2 children match search
                        $level2_search_match = false;
                        foreach ($level1->children as $level2) {
                            if (stripos($level2->kode_kategori, $search_value) !== false ||
                                stripos($level2->nama_kategori, $search_value) !== false) {
                                $level2_search_match = true;
                                break;
                            }
                        }
                        if (!$level2_search_match) $display_level1 = false;
                    }
                }
                
                if ($display_level1) {
                    $level1_status_badge = $level1->status == 1
                        ? '<span class="label label-success">Aktif</span>'
                        : '<span class="label label-danger">Nonaktif</span>';

                    $level1_actions = $this->generate_actions($level1);
                    
                    $level1_child_count = count($level1->children);
                    $level1_display = '<i class="fa fa-long-arrow-right"></i> ' . $level1->nama_kategori;
                    if ($level1_child_count > 0) {
                        $level1_display .= ' <small class="text-muted">(' . $level1_child_count . ' sub)</small>';
                    }

                    $result[] = [
                        'DT_RowClass' => 'child-row level-1',
                        '',
                        $level1->kode_kategori,
                        $level1_display,
                        $root->nama_kategori,
                        $level1_status_badge,
                        $level1_actions
                    ];
                }

                // Add Level 2 children (grandchildren)
                foreach ($level1->children as $level2) {
                    $display_level2 = true;
                    
                    // Apply filters for level 2
                    if (!empty($parent_filter)) {
                        if ($parent_filter != $root->id && $parent_filter != $level1->id && $parent_filter != $level2->id) {
                            $display_level2 = false;
                        }
                    }
                    
                    if (!empty($status_filter) && $status_filter !== '' && $level2->status != $status_filter) {
                        $display_level2 = false;
                    }
                    
                    if (!empty($search_value)) {
                        $level2_matches_search = (stripos($level2->kode_kategori, $search_value) !== false ||
                                                 stripos($level2->nama_kategori, $search_value) !== false);
                        if (!$level2_matches_search) $display_level2 = false;
                    }
                    
                    if ($display_level2) {
                        $level2_status_badge = $level2->status == 1
                            ? '<span class="label label-success">Aktif</span>'
                            : '<span class="label label-danger">Nonaktif</span>';

                        $level2_actions = $this->generate_actions($level2);
                        
                        $level2_display = '<i class="fa fa-angle-double-right"></i> ' . $level2->nama_kategori;

                        $result[] = [
                            'DT_RowClass' => 'grandchild-row level-2',
                            '',
                            $level2->kode_kategori,
                            $level2_display,
                            $level1->nama_kategori,
                            $level2_status_badge,
                            $level2_actions
                        ];
                    }
                }
            }
        }

        // Implement pagination
        $start = intval($this->input->post('start'));
        $length = intval($this->input->post('length'));

        $recordsFiltered = count($result);

        if ($length != -1) {
            $result = array_slice($result, $start, $length);
        }

        echo json_encode([
            'draw' => intval($this->input->post('draw')),
            'recordsTotal' => $this->master_kategori_m->count_all_kategori(),
            'recordsFiltered' => $recordsFiltered,
            'data' => $result
        ]);
    }
    
    private function generate_actions($kategori)
    {
        $actions = '';
        if (ce_hak_akses('admin.master_kategori.update')) {
            $actions .= '<button type="button" class="btn btn-warning btn-xs btn-edit"
                data-id="' . $kategori->id . '"
                data-parent="' . $kategori->parent_id . '"
                data-kode="' . $kategori->kode_kategori . '"
                data-nama="' . $kategori->nama_kategori . '"
                data-deskripsi="' . htmlspecialchars($kategori->deskripsi) . '"
                data-status="' . $kategori->status . '"
                title="Edit">
                <i class="fa fa-edit"></i>
            </button> ';
        }

        if (ce_hak_akses('admin.master_kategori.delete')) {
            $actions .= '<button type="button" class="btn btn-danger btn-xs btn-delete"
                data-id="' . $kategori->id . '"
                title="Hapus">
                <i class="fa fa-trash"></i>
            </button>';
        }
        
        return $actions;
    }
    
    private function count_all_descendants($children)
    {
        $count = count($children);
        foreach ($children as $child) {
            if (isset($child->children)) {
                $count += count($child->children);
            }
        }
        return $count;
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_kategori.add');
        } else {
            ce_hak_akses('admin.master_kategori.update');
        }

        $parent_id = $this->input->post('parent_id');
        $kode_kategori = $this->input->post('kode_kategori');
        $nama_kategori = $this->input->post('nama_kategori');
        $deskripsi = $this->input->post('deskripsi');
        $status = $this->input->post('status') ? 1 : 0;

        if (empty($kode_kategori)) {
            $response = [
                'status' => false,
                'message' => 'Kode kategori harus diisi'
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($nama_kategori)) {
            $response = [
                'status' => false,
                'message' => 'Nama kategori harus diisi'
            ];
            
            echo json_encode($response);
            return;
        }

        // Validasi maksimal 3 level hierarki
        if (!empty($parent_id)) {
            $parent_level = $this->master_kategori_m->get_kategori_level($parent_id);
            if ($parent_level >= 2) {
                $response = [
                    'status' => false,
                    'message' => 'Maksimal 3 level hierarki. Parent yang dipilih sudah berada di level 2.'
                ];
                
                echo json_encode($response);
                return;
            }
        }

        // Check if kode_kategori already exists
        $existing = $this->master_kategori_m->kategori_by_kode($kode_kategori);
        if ($existing && $existing->id != $id) {
            $response = [
                'status' => false,
                'message' => 'Kode kategori sudah digunakan'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'parent_id' => !empty($parent_id) ? $parent_id : null,
            'kode_kategori' => $kode_kategori,
            'nama_kategori' => $nama_kategori,
            'deskripsi' => $deskripsi,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            // Insert new record
            $data['created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_kategori_m->kategori_insert_data($data);
            $message = 'Data kategori berhasil ditambahkan';
        } else {
            // Update existing record
            $result = $this->master_kategori_m->kategori_update_data($data, $id);
            $message = 'Data kategori berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data kategori'
            ];
        }

        
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_kategori.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID kategori tidak valid'
            ];
            
            echo json_encode($response);
            return;
        }

        // Check if kategori has children
        $children = $this->master_kategori_m->kategori_get_children($id);
        if (!empty($children)) {
            $response = [
                'status' => false,
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki sub kategori'
            ];
            
            echo json_encode($response);
            return;
        }

        $result = $this->master_kategori_m->kategori_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data kategori berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data kategori'
            ];
        }

        
        echo json_encode($response);
    }
}
