<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Master_jenis_pasar extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.master_jenis_pasar.view');
    }

    public function index()
    {
        ce_hak_akses('admin.master_jenis_pasar.view');
        $data['header'] = 'Master <small>Jenis Pasar</small>';
        $data['halaman'] = 'master_jenis_pasar';
        $data['javascript'] = array(
            'master_jenis_pasar/js_master_jenis_pasar' => null
        );
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.master_jenis_pasar.view');

        $dataConfig = [
            'table' => 'master_pasar_jenis mpj',
            'select' => 'mpj.*, mp.pasar_nama,
                        (SELECT COUNT(*) FROM master_pasar_blok mpb WHERE mpb.pasar_jenis_id = mpj.pasar_jenis_id) as jumlah_blok,
                        (SELECT COUNT(*) FROM master_pasar_blok mpb WHERE mpb.pasar_jenis_id = mpj.pasar_jenis_id AND mpb.pasar_blok_status = "TERSEDIA") as blok_tersedia,
                        (SELECT COUNT(*) FROM master_pasar_blok mpb WHERE mpb.pasar_jenis_id = mpj.pasar_jenis_id AND mpb.pasar_blok_status = "TERISI") as blok_terisi',
            'column_order' => [null, 'mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpj.pasar_jenis_jumlah', 'mpj.pasar_jenis_tersedia', 'mpj.pasar_jenis_terisi', 'mpj.pasar_jenis_harga_sewa', 'mpj.pasar_jenis_status_sewa', 'jumlah_blok', null],
            'column_search' => ['mp.pasar_nama', 'mpj.pasar_jenis_nama', 'mpj.pasar_jenis_status_sewa'],
            'join' => [
                ['master_pasar mp', 'mpj.pasar_id=mp.pasar_id', 'left']
            ],
            'order' => ['mpj.pasar_jenis_id' => 'desc']
        ];

        // Filter berdasarkan parameter
        $where = [];

        if ($this->input->post('filter_pasar') && $this->input->post('filter_pasar') != '') {
            $where['mpj.pasar_id'] = $this->input->post('filter_pasar');
        }

        if ($this->input->post('filter_jenis') && $this->input->post('filter_jenis') != '') {
            $where['mpj.pasar_jenis_nama'] = $this->input->post('filter_jenis');
        }

        if ($this->input->post('filter_status_sewa') && $this->input->post('filter_status_sewa') != '') {
            $where['mpj.pasar_jenis_status_sewa'] = $this->input->post('filter_status_sewa');
        }

        if (!empty($where)) {
            $dataConfig['where'] = $where;
        }

        $list = $this->master_jenis_pasar_m->get_datatables(
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
        $no = $_POST['start'];
        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $item->pasar_nama;
            $row[] = '<span class="badge badge-info">' . $item->pasar_jenis_nama . '</span>';
            $row[] = number_format($item->jumlah_blok);
            $row[] = number_format($item->blok_tersedia);
            $row[] = number_format($item->blok_terisi);
            $row[] = 'Rp ' . number_format($item->pasar_jenis_harga_sewa, 0, ',', '.');
            
            // Status sewa dengan badge
            $status_badge = '';
            switch ($item->pasar_jenis_status_sewa) {
                case 'TERSEDIA':
                    $status_badge = '<span class="badge badge-success">TERSEDIA</span>';
                    break;
                case 'PENUH':
                    $status_badge = '<span class="badge badge-danger">PENUH</span>';
                    break;
                case 'MAINTENANCE':
                    $status_badge = '<span class="badge badge-warning">MAINTENANCE</span>';
                    break;
            }
            $row[] = $status_badge;

            // Jumlah blok dengan link ke denah
            $jumlah_blok_display = '<span class="badge badge-primary">' . $item->jumlah_blok . ' Blok</span>';
            if ($item->jumlah_blok > 0) {
                $jumlah_blok_display .= '<br><a href="' . base_url('master_pasar_blok?filter_jenis=' . urlencode($item->pasar_jenis_nama)) . '" class="btn btn-xs btn-info" target="_blank" title="Lihat Denah Blok"><i class="fa fa-th-large"></i> Lihat Denah</a>';
            }
            $row[] = $jumlah_blok_display;
            
            // Action buttons
            $aksi = '<div class="btn-group">';
            if (ce_hak_akses('admin.master_jenis_pasar.update')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit"
                    data-id="'.$item->pasar_jenis_id.'"
                    data-pasar-id="'.$item->pasar_id.'"
                    data-jenis="'.$item->pasar_jenis_nama.'"
                    data-jumlah="'.$item->jumlah_blok.'"
                    data-tersedia="'.$item->blok_tersedia.'"
                    data-terisi="'.$item->blok_terisi.'"
                    data-harga="'.($item->pasar_jenis_harga_sewa ?? 0).'"
                    data-status="'.$item->pasar_jenis_status_sewa.'"
                    data-keterangan="'.($item->pasar_jenis_keterangan ?? '').'"
                    ><i class="fa fa-edit"></i></button>';
            }
            if (ce_hak_akses('admin.master_jenis_pasar.delete')) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->pasar_jenis_id.'" data-nama="'.$item->pasar_nama.' - '.$item->pasar_jenis_nama.'"><i class="fa fa-trash"></i></button>';
            }
            $aksi .= '</div>';
            
            $row[] = $aksi;
            
            $data[] = $row;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->master_jenis_pasar_m->count_all(
                $dataConfig['table'],
                $dataConfig['join']
            ),
            "recordsFiltered" => $this->master_jenis_pasar_m->count_filtered(
                $dataConfig['table'],
                $dataConfig['column_order'],
                $dataConfig['column_search'],
                $dataConfig['order'],
                $dataConfig['join'],
                isset($dataConfig['where']) ? $dataConfig['where'] : null,
                null,
                $dataConfig['select']
            ),
            "data" => $data,
        );

        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.master_jenis_pasar.add');
        } else {
            ce_hak_akses('admin.master_jenis_pasar.update');
        }

        $pasar_id = $this->input->post('pasar_id');
        $pasar_jenis_nama = $this->input->post('pasar_jenis_nama');
        $pasar_jenis_jumlah = $this->input->post('pasar_jenis_jumlah');
        $pasar_jenis_tersedia = $this->input->post('pasar_jenis_tersedia');
        $pasar_jenis_terisi = $this->input->post('pasar_jenis_terisi');
        $pasar_jenis_harga_sewa = $this->input->post('pasar_jenis_harga_sewa');
        $pasar_jenis_status_sewa = $this->input->post('pasar_jenis_status_sewa');
        $pasar_jenis_keterangan = $this->input->post('pasar_jenis_keterangan');

        if (empty($pasar_id)) {
            $response = [
                'status' => false,
                'message' => 'Pasar tidak boleh kosong'
            ];

            echo json_encode($response);
            return;
        }

        if (empty($pasar_jenis_nama)) {
            $response = [
                'status' => false,
                'message' => 'Jenis Pasar tidak boleh kosong'
            ];

            echo json_encode($response);
            return;
        }

        $data = [
            'pasar_id' => $pasar_id,
            'pasar_jenis_nama' => $pasar_jenis_nama,
            'pasar_jenis_jumlah' => $pasar_jenis_jumlah ? $pasar_jenis_jumlah : 0,
            'pasar_jenis_tersedia' => $pasar_jenis_tersedia ? $pasar_jenis_tersedia : 0,
            'pasar_jenis_terisi' => $pasar_jenis_terisi ? $pasar_jenis_terisi : 0,
            'pasar_jenis_harga_sewa' => $pasar_jenis_harga_sewa ? $pasar_jenis_harga_sewa : 0,
            'pasar_jenis_status_sewa' => $pasar_jenis_status_sewa ? $pasar_jenis_status_sewa : 'TERSEDIA',
            'pasar_jenis_keterangan' => $pasar_jenis_keterangan,
            'pasar_jenis_updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($id)) {
            $data['pasar_jenis_created_at'] = date('Y-m-d H:i:s');
            $result = $this->master_jenis_pasar_m->master_jenis_pasar_insert_data($data);
            $message = 'Data jenis pasar berhasil ditambahkan';
        } else {
            $result = $this->master_jenis_pasar_m->master_jenis_pasar_update_data($data, $id);
            $message = 'Data jenis pasar berhasil diperbarui';
        }

        $response = [
            'status' => $result,
            'message' => $message
        ];

        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.master_jenis_pasar.delete');

        $id = $this->input->post('id');

        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
        } else {
            $result = $this->master_jenis_pasar_m->master_jenis_pasar_delete_data($id);
            $message = $result ? 'Data berhasil dihapus' : 'Gagal menghapus data';

            $response = [
                'status' => $result,
                'message' => $message
            ];
        }

        echo json_encode($response);
    }

    public function get_by_id($id)
    {
        ce_hak_akses('admin.master_jenis_pasar.read');

        $result = $this->master_jenis_pasar_m->master_jenis_pasar_by_id($id);

        if ($result) {
            // Hitung data real-time dari master_pasar_blok
            $jumlah_blok = $this->db->where('pasar_jenis_id', $id)->count_all_results('master_pasar_blok');
            $blok_tersedia = $this->db->where('pasar_jenis_id', $id)->where('pasar_blok_status', 'TERSEDIA')->count_all_results('master_pasar_blok');
            $blok_terisi = $this->db->where('pasar_jenis_id', $id)->where('pasar_blok_status', 'TERISI')->count_all_results('master_pasar_blok');

            $result->jumlah_blok = $jumlah_blok;
            $result->blok_tersedia = $blok_tersedia;
            $result->blok_terisi = $blok_terisi;

            $response = [
                'status' => 'success',
                'data' => $result
            ];
        } else {
            $response = [
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ];
        }

        echo json_encode($response);
    }

    public function get_jenis_pasar($pasar_id)
    {
        $result = $this->master_jenis_pasar_m->master_jenis_pasar_by_pasar_id($pasar_id);
        echo json_encode($result);
    }

    public function sinkronisasi_data()
    {
        ce_hak_akses('admin.master_jenis_pasar.update');

        $id = $this->input->post('id');

        if ($id) {
            // Sinkronisasi data spesifik
            $result = $this->master_jenis_pasar_m->sinkronisasi_data_blok($id);
        } else {
            // Sinkronisasi semua data
            $result = $this->master_jenis_pasar_m->sinkronisasi_data_blok();
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data berhasil disinkronisasi'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal melakukan sinkronisasi data'
            ];
        }

        echo json_encode($response);
    }

    public function get_statistik()
    {
        $data = [
            'total_jenis' => $this->master_jenis_pasar_m->get_total_jenis(),
            'total_tersedia' => $this->master_jenis_pasar_m->get_total_tersedia(),
            'total_penuh' => $this->master_jenis_pasar_m->get_total_penuh(),
            'total_maintenance' => $this->master_jenis_pasar_m->get_total_maintenance()
        ];

        echo json_encode($data);
    }
}
