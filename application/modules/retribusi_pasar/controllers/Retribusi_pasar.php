<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Retribusi_pasar extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.retribusi_pasar.view');
        $this->load->helper('ce');
    }

    public function index()
    {
        ce_hak_akses('admin.retribusi_pasar.view');

        $data['header'] = 'Retribusi <small>Pasar</small>';
        $data['halaman'] = 'retribusi_pasar';
        
        // Statistik untuk dashboard
        $bulan_ini = date('n');
        $tahun_ini = date('Y');
        $data['statistik'] = $this->retribusi_pasar_m->get_statistik_pembayaran($bulan_ini, $tahun_ini);
        $data['pedagang_belum_bayar'] = $this->retribusi_pasar_m->get_pedagang_belum_bayar($bulan_ini, $tahun_ini);
        
        // Data untuk dropdown filter
        $data['pasar_options'] = $this->master_pasar_m->master_pasar_get_active();
        $data['jenis_pasar_options'] = $this->retribusi_tarif_m->get_jenis_pasar_options();
        
        $data['javascript'] = array(
            'retribusi_pasar/js_retribusi_pasar' => null
        );
        
        $this->load->view('template', $data);
    }

    public function ajax_pedagang_list()
    {
        // Cek hak akses untuk AJAX request
        if (!ce_hak_akses('admin.retribusi_pasar.view', false)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Akses ditolak']);
            return;
        }

        $pasar_id = $this->input->post('pasar_id');
        $jenis_id = $this->input->post('jenis_id');
        $status_filter = $this->input->post('status_filter');
        $search = $this->input->post('search');
        
        $bulan = $this->input->post('bulan') ?: date('n');
        $tahun = $this->input->post('tahun') ?: date('Y');

        // Query pedagang dengan status pembayaran
        $this->db->select('
            mp.id, mp.nama_lengkap, mp.nik, mp.no_telpon,
            mpb.pasar_blok_nama, mpb.pasar_blok_nomor,
            mpj.pasar_jenis_nama, mps.pasar_nama,
            pb.tanggal_mulai, pb.tanggal_selesai,
            rpt.retribusi_status, rpt.retribusi_total, rpt.retribusi_tanggal
        ');
        $this->db->from('masyarakat_pedagang mp');
        $this->db->join('penyewaan_blok pb', 'mp.id = pb.pedagang_id AND pb.status = "AKTIF"', 'inner');
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mps', 'mpj.pasar_id = mps.pasar_id', 'left');
        $this->db->join('retribusi_pasar_transaksi rpt', 
            'mp.id = rpt.pedagang_id AND rpt.retribusi_periode_bulan = ' . $bulan . ' AND rpt.retribusi_periode_tahun = ' . $tahun, 
            'left');
        
        $this->db->where('mp.status_aktif', 1);
        
        // Filter berdasarkan pasar
        if ($pasar_id) {
            $this->db->where('mps.pasar_id', $pasar_id);
        }
        
        // Filter berdasarkan jenis pasar
        if ($jenis_id) {
            $this->db->where('mpj.pasar_jenis_id', $jenis_id);
        }
        
        // Filter berdasarkan status pembayaran
        if ($status_filter) {
            if ($status_filter == 'BELUM_BAYAR') {
                $this->db->where('rpt.retribusi_id IS NULL');
            } else {
                $this->db->where('rpt.retribusi_status', $status_filter);
            }
        }
        
        // Search
        if ($search) {
            $this->db->group_start();
            $this->db->like('mp.nama_lengkap', $search);
            $this->db->or_like('mp.nik', $search);
            $this->db->or_like('mpb.pasar_blok_nama', $search);
            $this->db->group_end();
        }
        
        $this->db->order_by('mp.nama_lengkap', 'asc');
        $pedagang_list = $this->db->get()->result();

        $html = '';
        foreach ($pedagang_list as $pedagang) {
            $status_class = 'default';
            $status_text = 'Belum Bayar';
            
            if ($pedagang->retribusi_status) {
                switch ($pedagang->retribusi_status) {
                    case 'LUNAS':
                        $status_class = 'success';
                        $status_text = 'Lunas';
                        break;
                    case 'TERLAMBAT':
                        $status_class = 'warning';
                        $status_text = 'Terlambat';
                        break;
                    case 'SEBAGIAN':
                        $status_class = 'info';
                        $status_text = 'Sebagian';
                        break;
                }
            }

            $html .= '<a href="#" class="list-group-item pedagang-item" data-id="' . $pedagang->id . '">';
            $html .= '<div class="row">';
            $html .= '<div class="col-xs-8">';
            $html .= '<h5 class="list-group-item-heading">' . $pedagang->nama_lengkap . '</h5>';
            $html .= '<p class="list-group-item-text">';
            $html .= '<small class="text-muted">NIK: ' . $pedagang->nik . '</small><br>';
            $html .= '<small class="text-primary">' . $pedagang->pasar_nama . ' - ' . $pedagang->pasar_blok_nama . '</small>';
            $html .= '</p>';
            $html .= '</div>';
            $html .= '<div class="col-xs-4 text-right">';
            $html .= '<span class="label label-' . $status_class . '">' . $status_text . '</span>';
            if ($pedagang->retribusi_total) {
                $html .= '<br><small class="text-success">Rp ' . number_format($pedagang->retribusi_total, 0, ',', '.') . '</small>';
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</a>';
        }

        if (empty($pedagang_list)) {
            $html = '<div class="text-center text-muted" style="padding: 20px;">Tidak ada data pedagang</div>';
        }

        $response = [
            'status' => true,
            'html' => $html,
            'total' => count($pedagang_list)
        ];

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function ajax_detail_pembayaran()
    {
        // Cek hak akses untuk AJAX request
        if (!ce_hak_akses('admin.retribusi_pasar.view', false)) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Akses ditolak']);
            return;
        }

        $pedagang_id = $this->input->post('pedagang_id');
        $bulan = $this->input->post('bulan') ?: date('n');
        $tahun = $this->input->post('tahun') ?: date('Y');

        if (!$pedagang_id) {
            $response = [
                'status' => false,
                'message' => 'ID Pedagang tidak valid'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Get data pedagang dengan detail blok
        $this->db->select('
            mp.*, mpb.pasar_blok_nama, mpb.pasar_blok_nomor, mpb.pasar_blok_harga_sewa,
            mpj.pasar_jenis_nama, mps.pasar_nama, pb.tanggal_mulai, pb.tanggal_selesai
        ');
        $this->db->from('masyarakat_pedagang mp');
        $this->db->join('penyewaan_blok pb', 'mp.id = pb.pedagang_id AND pb.status = "AKTIF"', 'inner');
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->join('master_pasar mps', 'mpj.pasar_id = mps.pasar_id', 'left');
        $this->db->where('mp.id', $pedagang_id);
        $pedagang = $this->db->get()->row();

        if (!$pedagang) {
            $response = [
                'status' => false,
                'message' => 'Data pedagang tidak ditemukan'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Get data pembayaran untuk periode ini
        $pembayaran = $this->db->where('pedagang_id', $pedagang_id)
                              ->where('retribusi_periode_bulan', $bulan)
                              ->where('retribusi_periode_tahun', $tahun)
                              ->get('retribusi_pasar_transaksi')->row();

        // Get tarif yang berlaku
        $tarif = $this->retribusi_tarif_m->get_by_jenis_pasar($pedagang->pasar_jenis_id);

        // Generate HTML detail
        $html = $this->load->view('retribusi_pasar/detail_pembayaran', [
            'pedagang' => $pedagang,
            'pembayaran' => $pembayaran,
            'tarif' => $tarif,
            'bulan' => $bulan,
            'tahun' => $tahun
        ], true);

        $response = [
            'status' => true,
            'html' => $html,
            'pedagang' => $pedagang,
            'pembayaran' => $pembayaran,
            'tarif' => $tarif
        ];

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.retribusi_pasar.add');
        } else {
            ce_hak_akses('admin.retribusi_pasar.update');
        }

        $pedagang_id = $this->input->post('pedagang_id');
        $pasar_blok_id = $this->input->post('pasar_blok_id');
        $penyewaan_blok_id = $this->input->post('penyewaan_blok_id');
        $retribusi_tanggal = $this->input->post('retribusi_tanggal') ?: date('Y-m-d');
        $retribusi_periode_bulan = $this->input->post('retribusi_periode_bulan');
        $retribusi_periode_tahun = $this->input->post('retribusi_periode_tahun');
        $retribusi_nominal = $this->input->post('retribusi_nominal');
        $retribusi_denda = $this->input->post('retribusi_denda') ?: 0;
        $retribusi_status = $this->input->post('retribusi_status') ?: 'LUNAS';
        $retribusi_metode_bayar = $this->input->post('retribusi_metode_bayar') ?: 'TUNAI';
        $retribusi_keterangan = $this->input->post('retribusi_keterangan');

        $data = [
            'pedagang_id' => $pedagang_id,
            'pasar_blok_id' => $pasar_blok_id,
            'penyewaan_blok_id' => $penyewaan_blok_id,
            'retribusi_tanggal' => $retribusi_tanggal,
            'retribusi_periode_bulan' => $retribusi_periode_bulan,
            'retribusi_periode_tahun' => $retribusi_periode_tahun,
            'retribusi_nominal' => $retribusi_nominal,
            'retribusi_denda' => $retribusi_denda,
            'retribusi_total' => $retribusi_nominal + $retribusi_denda,
            'retribusi_status' => $retribusi_status,
            'retribusi_metode_bayar' => $retribusi_metode_bayar,
            'retribusi_keterangan' => $retribusi_keterangan,
            'retribusi_petugas_id' => $this->session->id_user
        ];

        // Validasi data
        $errors = $this->retribusi_pasar_m->validate_retribusi_data($data, $id);
        if (!empty($errors)) {
            $response = [
                'status' => false,
                'message' => implode('<br>', $errors)
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        if (empty($id)) {
            $result = $this->retribusi_pasar_m->retribusi_insert_data($data);
            $message = 'Pembayaran retribusi berhasil dicatat';
        } else {
            $result = $this->retribusi_pasar_m->retribusi_update_data($data, $id);
            $message = 'Data pembayaran berhasil diperbarui';
        }

        if ($result) {
            $response = [
                'status' => true,
                'message' => $message
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan data pembayaran'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function delete()
    {
        ce_hak_akses('admin.retribusi_pasar.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            $response = [
                'status' => false,
                'message' => 'ID tidak valid'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $result = $this->retribusi_pasar_m->retribusi_delete_data($id);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Data pembayaran berhasil dihapus'
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menghapus data pembayaran'
            ];
        }

        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function hitung_denda()
    {
        $nominal = $this->input->post('nominal');
        $tanggal_jatuh_tempo = $this->input->post('tanggal_jatuh_tempo');
        $tanggal_bayar = $this->input->post('tanggal_bayar') ?: date('Y-m-d');
        $persen_denda = $this->input->post('persen_denda') ?: 2.5;

        $denda = $this->retribusi_pasar_m->hitung_denda($nominal, $tanggal_jatuh_tempo, $tanggal_bayar, $persen_denda);

        $response = [
            'status' => true,
            'denda' => $denda,
            'total' => $nominal + $denda
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function bayar_retribusi_harian()
    {
        // Cek hak akses
        ce_hak_akses('admin.retribusi_pasar.add');

        $pedagang_id = $this->input->post('pedagang_id');
        $tanggal_bayar = $this->input->post('tanggal_bayar') ?: date('Y-m-d');
        $metode_bayar = $this->input->post('metode_bayar') ?: 'TUNAI';
        $keterangan = $this->input->post('keterangan');

        // Validasi input
        if (empty($pedagang_id)) {
            $response = [
                'status' => false,
                'message' => 'Pedagang harus dipilih'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Ambil data pedagang dan blok
        $pedagang_data = $this->get_pedagang_with_blok($pedagang_id);
        if (!$pedagang_data) {
            $response = [
                'status' => false,
                'message' => 'Data pedagang tidak ditemukan atau tidak memiliki blok aktif'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Ambil tarif berdasarkan jenis pasar
        $tarif = $this->retribusi_tarif_m->get_tarif_aktif_by_jenis($pedagang_data->pasar_jenis_id);
        if (!$tarif) {
            $response = [
                'status' => false,
                'message' => 'Tarif retribusi untuk jenis pasar ini belum ditetapkan'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Periode pembayaran (hari ini)
        $periode_bulan = date('n', strtotime($tanggal_bayar));
        $periode_tahun = date('Y', strtotime($tanggal_bayar));

        // Cek apakah sudah ada pembayaran untuk hari ini
        $existing = $this->retribusi_pasar_m->cek_pembayaran_harian($pedagang_id, $tanggal_bayar);
        if ($existing) {
            $response = [
                'status' => false,
                'message' => 'Pembayaran retribusi untuk tanggal ' . date('d/m/Y', strtotime($tanggal_bayar)) . ' sudah ada'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        // Hitung denda jika terlambat (asumsi jatuh tempo setiap hari)
        $denda = 0;
        $status = 'LUNAS';

        // Data pembayaran
        $data = [
            'pedagang_id' => $pedagang_id,
            'pasar_blok_id' => $pedagang_data->pasar_blok_id,
            'penyewaan_blok_id' => $pedagang_data->penyewaan_blok_id,
            'retribusi_tanggal' => $tanggal_bayar,
            'retribusi_periode_bulan' => $periode_bulan,
            'retribusi_periode_tahun' => $periode_tahun,
            'retribusi_nominal' => $tarif->tarif_harian,
            'retribusi_denda' => $denda,
            'retribusi_total' => $tarif->tarif_harian + $denda,
            'retribusi_status' => $status,
            'retribusi_metode_bayar' => $metode_bayar,
            'retribusi_keterangan' => $keterangan,
            'retribusi_petugas_id' => $this->session->id_user
        ];

        // Simpan pembayaran
        $result = $this->retribusi_pasar_m->retribusi_insert_data($data);

        if ($result) {
            $response = [
                'status' => true,
                'message' => 'Pembayaran retribusi harian berhasil dicatat',
                'data' => [
                    'pedagang_nama' => $pedagang_data->nama_lengkap,
                    'nominal' => number_format($tarif->tarif_harian, 0, ',', '.'),
                    'tanggal' => date('d/m/Y', strtotime($tanggal_bayar))
                ]
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Gagal menyimpan pembayaran retribusi'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    public function get_tarif_by_jenis()
    {
        $jenis_id = $this->input->post('jenis_id');

        if (empty($jenis_id)) {
            $response = [
                'status' => false,
                'message' => 'ID jenis pasar tidak valid'
            ];
            header('Content-Type: application/json');
            echo json_encode($response);
            return;
        }

        $tarif = $this->retribusi_tarif_m->get_tarif_aktif_by_jenis($jenis_id);

        if ($tarif) {
            $response = [
                'status' => true,
                'data' => [
                    'tarif_id' => $tarif->tarif_id,
                    'tarif_nama' => $tarif->tarif_nama,
                    'tarif_harian' => $tarif->tarif_harian,
                    'tarif_bulanan' => $tarif->tarif_bulanan,
                    'tarif_denda_persen' => $tarif->tarif_denda_persen
                ]
            ];
        } else {
            $response = [
                'status' => false,
                'message' => 'Tarif untuk jenis pasar ini belum ditetapkan'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response);
    }

    private function get_pedagang_with_blok($pedagang_id)
    {
        $this->db->select('mp.*, pb.id as penyewaan_blok_id, pb.pasar_blok_id, mpb.pasar_jenis_id, mpj.pasar_jenis_nama');
        $this->db->from('masyarakat_pedagang mp');
        $this->db->join('penyewaan_blok pb', 'mp.id = pb.pedagang_id AND pb.status = "AKTIF"', 'inner');
        $this->db->join('master_pasar_blok mpb', 'pb.pasar_blok_id = mpb.pasar_blok_id', 'left');
        $this->db->join('master_pasar_jenis mpj', 'mpb.pasar_jenis_id = mpj.pasar_jenis_id', 'left');
        $this->db->where('mp.id', $pedagang_id);
        $this->db->where('mp.status_aktif', 1);

        return $this->db->get()->row();
    }
}
