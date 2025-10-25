<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_pasar_blok extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_pasar_blok.view');
        $this->load->model('Master_pasar_unit_m');
    }

    public function index()
    {
        ce_hak_akses('admin.master_pasar_blok.view');
        $data['header'] = 'Master <small>Pasar Blok</small>';
        $data['halaman'] = 'master_pasar_blok';
        $data['javascript'] = array(
            'master_pasar_blok/js_master_pasar_blok' => null,
            'master_pasar_blok/js_denah_unit' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_pasar_blok.view');

        $dataConfig = [
            'table' => 'master_pasar_blok mpb',
            'select' => 'mpb.pasar_blok_id, mpb.pasar_jenis_id, mpb.pasar_blok_nama, mpb.pasar_blok_nomor, mpb.pasar_blok_luas, mpb.pasar_blok_status, mpb.pasar_blok_posisi_x, mpb.pasar_blok_posisi_y, mpb.pasar_blok_keterangan, mpj.pasar_jenis_nama, mp.pasar_nama,
                       (SELECT COUNT(*) FROM master_pasar_unit mpu WHERE mpu.pasar_blok_id = mpb.pasar_blok_id) as total_unit,
                       (SELECT COUNT(*) FROM master_pasar_unit mpu WHERE mpu.pasar_blok_id = mpb.pasar_blok_id AND mpu.pasar_unit_status = "TERSEDIA") as unit_tersedia,
                       (SELECT COUNT(*) FROM master_pasar_unit mpu WHERE mpu.pasar_blok_id = mpb.pasar_blok_id AND mpu.pasar_unit_status = "TERISI") as unit_terisi',
            'column_order' => [null, 'mpb.pasar_blok_nama', 'mpb.pasar_blok_nomor', 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpb.pasar_blok_luas', 'total_unit', 'unit_tersedia', 'unit_terisi', 'mpb.pasar_blok_status', 'mpb.pasar_blok_posisi_x', null],
            'column_search' => ['mpb.pasar_blok_nama', 'mpb.pasar_blok_nomor', 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpb.pasar_blok_status'],
            'join' => [
                ['master_pasar_jenis mpj', 'mpb.pasar_jenis_id=mpj.pasar_jenis_id', 'left'],
                ['master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left']
            ],
            'order' => ['mpb.pasar_blok_id' => 'desc']
        ];

        // Filter berdasarkan parameter
        $where = [];
        
        if ($this->input->post('filter_pasar') && $this->input->post('filter_pasar') != '') {
            $where['mp.pasar_id'] = $this->input->post('filter_pasar');
        }
        
        if ($this->input->post('filter_jenis') && $this->input->post('filter_jenis') != '') {
            $where['mpj.pasar_jenis_nama'] = $this->input->post('filter_jenis');
        }
        
        if ($this->input->post('filter_status') && $this->input->post('filter_status') != '') {
            $where['mpb.pasar_blok_status'] = $this->input->post('filter_status');
        }

        if (!empty($where)) {
            $dataConfig['where'] = $where;
        }

        $list = $this->master_pasar_blok_m->get_datatables(
            $dataConfig['table'],
            $dataConfig['column_order'],
            $dataConfig['column_search'],
            $dataConfig['order'],
            $dataConfig['join'],
            isset($dataConfig['where']) ? $dataConfig['where'] : null,
            null,
            $dataConfig['select']
        );

        $data = array();
        $start = isset($_POST['start']) ? $_POST['start'] : 0;
        $no = $start;
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->pasar_blok_nama;
            $row[] = $item->pasar_blok_nomor;
            $row[] = $item->pasar_nama;
            $row[] = $item->pasar_jenis_nama;
            $row[] = number_format($item->pasar_blok_luas, 2) . ' m²';
            
            // Unit information
            $unit_info = '<div style="text-align: center;">';
            $unit_info .= '<div><strong>' . $item->total_unit . '</strong></div>';
            $unit_info .= '<small class="text-success">Tersedia: ' . $item->unit_tersedia . '</small><br>';
            $unit_info .= '<small class="text-danger">Terisi: ' . $item->unit_terisi . '</small>';
            $unit_info .= '</div>';
            $row[] = $unit_info;
            
            // Auto status based on unit availability
            $auto_status = $item->pasar_blok_status;
            if ($item->total_unit > 0) {
                if ($item->unit_terisi >= $item->total_unit) {
                    $auto_status = 'FULL';
                } elseif ($item->unit_terisi > 0) {
                    $auto_status = 'TERISI';
                } else {
                    $auto_status = 'TERSEDIA';
                }
            }
            
            // Status badge
            $status_class = '';
            switch ($auto_status) {
                case 'TERSEDIA':
                    $status_class = 'label-success';
                    break;
                case 'TERISI':
                    $status_class = 'label-warning';
                    break;
                case 'FULL':
                    $status_class = 'label-danger';
                    break;
                case 'MAINTENANCE':
                    $status_class = 'label-warning';
                    break;
            }
            $row[] = '<span class="label ' . $status_class . '">' . $auto_status . '</span>';
            
            $row[] = '(' . $item->pasar_blok_posisi_x . ', ' . $item->pasar_blok_posisi_y . ')';
            
            // Action buttons
            $action = '';
            if (ce_hak_akses('admin.master_pasar_blok.update')) {
                $action .= '<button type="button" class="btn btn-xs btn-warning btn-edit"
                    data-id="' . $item->pasar_blok_id . '"
                    data-jenis-id="' . (isset($item->pasar_jenis_id) ? $item->pasar_jenis_id : '') . '"
                    data-nama="' . $item->pasar_blok_nama . '"
                    data-nomor="' . $item->pasar_blok_nomor . '"
                    data-luas="' . $item->pasar_blok_luas . '"
                    data-status="' . $item->pasar_blok_status . '"
                    data-keterangan="' . (isset($item->pasar_blok_keterangan) ? $item->pasar_blok_keterangan : '') . '"
                    data-x="' . $item->pasar_blok_posisi_x . '"
                    data-y="' . $item->pasar_blok_posisi_y . '"
                    title="Edit"><i class="fa fa-edit"></i></button> ';
            }
            
            if (ce_hak_akses('admin.master_pasar_blok.delete')) {
                $action .= '<button type="button" class="btn btn-xs btn-danger btn-delete"
                    data-id="' . $item->pasar_blok_id . '"
                    data-nama="' . $item->pasar_blok_nama . '"
                    title="Hapus"><i class="fa fa-trash"></i></button>';
            }
            
            $row[] = $action;
            $data[] = $row;
        }

        $draw = isset($_POST['draw']) ? $_POST['draw'] : 1;
        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->master_pasar_blok_m->count_all(
                $dataConfig['table'],
                $dataConfig['join'],
                isset($dataConfig['where']) ? $dataConfig['where'] : null
            ),
            "recordsFiltered" => $this->master_pasar_blok_m->count_filtered(
                $dataConfig['table'],
                $dataConfig['column_order'],
                $dataConfig['column_search'],
                $dataConfig['order'],
                $dataConfig['join'],
                isset($dataConfig['where']) ? $dataConfig['where'] : null
            ),
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_pasar_blok.add');
        } else {
            ce_hak_akses('admin.master_pasar_blok.update');
        }
        
        $pasar_jenis_id = $this->input->post('pasar_jenis_id');
        $pasar_blok_nama = $this->input->post('pasar_blok_nama');
        $pasar_blok_nomor = $this->input->post('pasar_blok_nomor');
        $pasar_blok_luas = $this->input->post('pasar_blok_luas');
        $pasar_blok_status = $this->input->post('pasar_blok_status');
        $pasar_blok_keterangan = $this->input->post('pasar_blok_keterangan');
        $pasar_blok_posisi_x = $this->input->post('pasar_blok_posisi_x');
        $pasar_blok_posisi_y = $this->input->post('pasar_blok_posisi_y');

        if (empty($pasar_jenis_id)) {
            $response = [
                'status' => false,
                'message' => 'Jenis Pasar tidak boleh kosong'
            ];
            
            echo json_encode($response);
            return;
        }

        if (empty($pasar_blok_nama)) {
            $response = [
                'status' => false,
                'message' => 'Nama Blok tidak boleh kosong'
            ];
            
            echo json_encode($response);
            return;
        }

        $data = [
            'pasar_jenis_id' => $pasar_jenis_id,
            'pasar_blok_nama' => $pasar_blok_nama,
            'pasar_blok_nomor' => $pasar_blok_nomor ? $pasar_blok_nomor : '',
            'pasar_blok_luas' => $pasar_blok_luas ? $pasar_blok_luas : 0,
            'pasar_blok_status' => $pasar_blok_status ? $pasar_blok_status : 'TERSEDIA',
            'pasar_blok_keterangan' => $pasar_blok_keterangan,
            'pasar_blok_posisi_x' => $pasar_blok_posisi_x ? $pasar_blok_posisi_x : 0,
            'pasar_blok_posisi_y' => $pasar_blok_posisi_y ? $pasar_blok_posisi_y : 0,
            'pasar_blok_updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['pasar_blok_created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_pasar_blok_m->master_pasar_blok_insert_data($data);
            $message = 'Data blok pasar berhasil ditambahkan';
        } else {
            $result = $this->master_pasar_blok_m->master_pasar_blok_update_data($data, $id);
            $message = 'Data blok pasar berhasil diperbarui';
        }

        $response = [
            'status' => $result,
            'message' => $message
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_pasar_blok.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
        } else {
            $result = $this->master_pasar_blok_m->master_pasar_blok_delete_data($id);
            $message = $result ? 'Data berhasil dihapus' : 'Gagal menghapus data';
            
            $response = [
                'status' => $result,
                'message' => $message
            ];
        }

        // Log untuk debugging
        log_message('debug', 'Master_pasar_blok delete response: ' . json_encode($response));
        echo json_encode($response);
    }

    // Method khusus untuk denah
    public function get_denah_data()
    {
        ce_hak_akses('admin.master_pasar_blok.view');
        
        $filter_pasar = $this->input->get('filter_pasar');
        $filter_jenis = $this->input->get('filter_jenis');
        $filter_status = $this->input->get('filter_status');
        
        $result = $this->master_pasar_blok_m->get_denah_data($filter_pasar, $filter_jenis, $filter_status);
        echo json_encode($result);
    }

    public function update_posisi()
    {
        ce_hak_akses('admin.master_pasar_blok.update');
        
        $id = $this->input->post('id');
        $x = $this->input->post('x');
        $y = $this->input->post('y');
        
        if (empty($id) || !is_numeric($x) || !is_numeric($y)) {
            $response = [
                'status' => false,
                'message' => 'Parameter tidak valid'
            ];
        } else {
            $result = $this->master_pasar_blok_m->update_posisi($id, $x, $y);
            $response = [
                'status' => $result,
                'message' => $result ? 'Posisi berhasil diperbarui' : 'Gagal memperbarui posisi'
            ];
        }
        
        // Log untuk debugging
        log_message('debug', 'Master_pasar_blok update_posisi response: ' . json_encode($response));
        echo json_encode($response);
    }

    public function get_statistik()
    {
        $data = [
            'total_blok' => $this->master_pasar_blok_m->get_total_blok(),
            'total_tersedia' => $this->master_pasar_blok_m->get_total_tersedia(),
            'total_terisi' => $this->master_pasar_blok_m->get_total_terisi(),
            'total_maintenance' => $this->master_pasar_blok_m->get_total_maintenance()
        ];
        
        echo json_encode($data);
    }

    public function get_jenis_pasar_options()
    {
        $pasar_id = $this->input->get('pasar_id');
        $jenis_list = $this->master_pasar_blok_m->get_jenis_pasar_options($pasar_id);
        $options = '<option value="">Pilih Jenis Pasar</option>';
        
        foreach ($jenis_list as $jenis) {
            $options .= '<option value="' . $jenis->pasar_jenis_id . '">' . $jenis->pasar_jenis_nama . '</option>';
        }
        
        echo $options;
    }

    public function get_jenis_pasar_by_id()
    {
        $id = $this->input->get('id');
        if (empty($id)) {
            echo json_encode(null);
            return;
        }
        
        $this->db->select('mpj.*, mp.pasar_id');
        $this->db->from('master_pasar_jenis mpj');
        $this->db->join('master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left');
        $this->db->where('mpj.pasar_jenis_id', $id);
        $result = $this->db->get()->row();
        
        echo json_encode($result);
    }

    // ============ UNIT METHODS ============
    
    // Get units by blok_id
    public function get_units_by_blok()
    {
        ce_hak_akses('admin.master_pasar_blok.view');
        
        $blok_id = $this->input->get('blok_id');
        if (empty($blok_id)) {
            echo json_encode([]);
            return;
        }
        
        $units = $this->Master_pasar_unit_m->get_units_by_blok_id($blok_id);
        echo json_encode($units);
    }

    // Get unit by id
    public function get_unit_by_id()
    {
        ce_hak_akses('admin.master_pasar_blok.view');
        
        $id = $this->input->get('id');
        if (empty($id)) {
            echo json_encode([]);
            return;
        }
        
        $unit = $this->Master_pasar_unit_m->get_unit_by_id($id);
        echo json_encode($unit);
    }

    // Save unit
    public function save_unit()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_pasar_blok.add');
        } else {
            ce_hak_akses('admin.master_pasar_blok.update');
        }
        
        $blok_id = $this->input->post('pasar_blok_id');
        $nomor = $this->input->post('pasar_unit_nomor');
        $lebar = $this->input->post('pasar_unit_lebar');
        $panjang = $this->input->post('pasar_unit_panjang');
        $harga = $this->input->post('pasar_unit_harga_sewa');
        $status = $this->input->post('pasar_unit_status');
        $penyewa = $this->input->post('pasar_unit_penyewa');
        $tanggal_sewa = $this->input->post('pasar_unit_tanggal_sewa');
        $tanggal_jatuh_tempo = $this->input->post('pasar_unit_tanggal_jatuh_tempo');
        $keterangan = $this->input->post('pasar_unit_keterangan');
        
        // Validate
        if (empty($blok_id) || empty($nomor)) {
            $response = [
                'status' => false,
                'message' => 'Blok dan Nomor Unit harus diisi'
            ];
            echo json_encode($response);
            return;
        }
        
        // Check if nomor exists
        if ($this->Master_pasar_unit_m->check_unit_nomor_exists($blok_id, $nomor, $id)) {
            $response = [
                'status' => false,
                'message' => 'Nomor Unit sudah ada dalam blok ini'
            ];
            echo json_encode($response);
            return;
        }
        
        $data = [
            'pasar_blok_id' => $blok_id,
            'pasar_unit_nomor' => $nomor,
            'pasar_unit_lebar' => $lebar ? $lebar : 0,
            'pasar_unit_panjang' => $panjang ? $panjang : 0,
            'pasar_unit_harga_sewa' => $harga ? $harga : 0,
            'pasar_unit_status' => $status ? $status : 'TERSEDIA',
            'pasar_unit_penyewa' => $status === 'TERISI' ? $penyewa : null,
            'pasar_unit_tanggal_sewa' => $status === 'TERISI' ? $tanggal_sewa : null,
            'pasar_unit_tanggal_jatuh_tempo' => $status === 'TERISI' ? $tanggal_jatuh_tempo : null,
            'pasar_unit_keterangan' => $keterangan
        ];
        
        if (empty($id)) {
            $result = $this->Master_pasar_unit_m->insert_unit($data);
            $message = 'Unit berhasil ditambahkan';
        } else {
            $result = $this->Master_pasar_unit_m->update_unit($data, $id);
            $message = 'Unit berhasil diperbarui';
        }
        
        $response = [
            'status' => $result,
            'message' => $message
        ];
        
        echo json_encode($response);
    }

    // Delete unit
    public function delete_unit()
    {
        ce_hak_akses('admin.master_pasar_blok.delete');
        
        $id = $this->input->post('id');
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
        } else {
            $result = $this->Master_pasar_unit_m->delete_unit($id);
            $response = [
                'status' => $result,
                'message' => $result ? 'Unit berhasil dihapus' : 'Gagal menghapus unit'
            ];
        }
        
        echo json_encode($response);
    }

    // Generate multiple units
    public function generate_units()
    {
        ce_hak_akses('admin.master_pasar_blok.add');
        
        $blok_id = $this->input->post('blok_id');
        $start_nomor = $this->input->post('start_nomor');
        $end_nomor = $this->input->post('end_nomor');
        $lebar = $this->input->post('lebar');
        $panjang = $this->input->post('panjang');
        $harga = $this->input->post('harga');
        
        if (empty($blok_id) || empty($start_nomor) || empty($end_nomor)) {
            $response = [
                'status' => false,
                'message' => 'Data tidak lengkap'
            ];
            echo json_encode($response);
            return;
        }
        
        $result = $this->Master_pasar_unit_m->generate_units(
            $blok_id,
            $start_nomor,
            $end_nomor,
            $lebar,
            $panjang,
            $harga
        );
        
        $response = [
            'status' => $result,
            'message' => $result ? 'Unit berhasil digenerate' : 'Gagal generate unit'
        ];
        
        echo json_encode($response);
    }

    // Get unit statistics
    public function get_unit_stats()
    {
        ce_hak_akses('admin.master_pasar_blok.view');
        
        $blok_id = $this->input->get('blok_id');
        $stats = $this->Master_pasar_unit_m->get_unit_stats($blok_id);
        echo json_encode($stats);
    }

    // Get expiring units
    public function get_expiring_units()
    {
        ce_hak_akses('admin.master_pasar_blok.view');
        
        $days = $this->input->get('days') ? $this->input->get('days') : 30;
        $units = $this->Master_pasar_unit_m->get_units_expiring_soon($days);
        echo json_encode($units);
    }

    // Bulk update status
    public function bulk_update_status()
    {
        ce_hak_akses('admin.master_pasar_blok.update');
        
        $ids = $this->input->post('ids');
        $status = $this->input->post('status');
        
        if (empty($ids) || empty($status)) {
            $response = [
                'status' => false,
                'message' => 'Parameter tidak lengkap'
            ];
            echo json_encode($response);
            return;
        }
        
        $result = $this->Master_pasar_unit_m->bulk_update_status($ids, $status);
        $response = [
            'status' => $result,
            'message' => $result ? 'Status berhasil diperbarui' : 'Gagal memperbarui status'
        ];
        
        echo json_encode($response);
    }
}
