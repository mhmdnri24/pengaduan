<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Beranda extends MX_Controller
{
	public function __construct()
	{
		parent::__construct();
		// Allow public access to the secure file serving method
		$public_methods = ['serve_secure_file'];
		if (!in_array($this->router->fetch_method(), $public_methods) && !$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		$this->load->model('beranda/beranda_m');
		$this->load->model('user/Log_login_m');
		$this->load->model('kategori_kepengurusan/kategori_kepengurusan_m');
		$this->load->model('fasilitas_umum/fasilitas_umum_m');
		$this->load->model('masyarakat/masyarakat_m');
	}

	public function index()
	{
		$user_id = $this->session->userdata('id_user');
		$data['usr'] = $this->user_m->user_by_id($user_id);
		
		// Default data untuk semua user
		$data['halaman'] = 'beranda';
		$data['header'] = 'Dashboard <small>Sistem Informasi Masyarakat</small>';
		$data['javascript'] = ['beranda/js_beranda' => null];
		
		// Semua data akan dimuat via AJAX
		$this->load->view('template', $data);
	}

	// AJAX Methods untuk memuat data dashboard

	public function ajax_detail_kategori_kepengurusan()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		// Ambil semua kategori kepengurusan yang aktif
		$kategoris = $this->kategori_kepengurusan_m->kategori_kepengurusan_get_active();
		$detail_data = [];

		foreach ($kategoris as $kategori) {
			// Hitung jumlah orang per kategori dari kepengurusan_detail
			$this->db->select('COUNT(*) as total_orang');
			$this->db->from('kepengurusan_detail');
			$this->db->where('kategori_kepengurusan_id', $kategori->kepengurusan_id);
			$this->db->where('status', 'AKTIF'); // status AKTIF bukan 1
			$count_result = $this->db->get()->row();

			$detail_data[] = [
				'kepengurusan_id' => $kategori->kepengurusan_id,
				'kepengurusan_kode' => $kategori->kepengurusan_kode,
				'kepengurusan_nama' => $kategori->kepengurusan_nama,
				'total_orang' => $count_result->total_orang
			];
		}

		$response = [
			'status' => true,
			'data' => $detail_data
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function ajax_detail_fasilitas_per_kategori()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		$id_kecamatan = $this->input->post('id_kecamatan');
		$id_kelurahan = $this->input->post('id_kelurahan');
		$id_kota = $this->input->post('id_kota') ?: '16.73';

        // Ambil kategori level 1 (child dari root) dengan jumlah fasilitas per kategori (filtered by lokasi)
        $this->db->select('root.id as parent_id, root.nama_kategori as parent_nama, mk.id, mk.nama_kategori, mk.kode_kategori, COALESCE(COUNT(fu.id), 0) as total_fasilitas');
        $this->db->from('master_kategori mk');
        $this->db->join('master_kategori root', 'mk.parent_id = root.id', 'inner');
        $this->db->join('fasilitas_umum fu', 'mk.id = fu.kategori_id AND fu.status = 1', 'left');
        $this->db->where('mk.status', 1);
        $this->db->where('root.parent_id IS NULL', null, false); // mk is level-1 (sub of root)
        // Filter lokasi
        $this->db->where('(fu.id_kota = '.$this->db->escape($id_kota).' OR fu.id_kota IS NULL)', null, false);
        if (!empty($id_kecamatan)) {
            $this->db->where('fu.id_kecamatan', $id_kecamatan);
        }
        if (!empty($id_kelurahan)) {
            $this->db->where('fu.id_kelurahan', $id_kelurahan);
        }
        $this->db->group_by('root.id, root.nama_kategori, mk.id, mk.nama_kategori, mk.kode_kategori');
        $this->db->order_by('total_fasilitas', 'desc');
        $this->db->order_by('mk.nama_kategori', 'asc');
        $rows = $this->db->get()->result();

        // Grouping by parent category
        $grouped = [];
        foreach ($rows as $row) {
            $pid = $row->parent_id ?: 0;
            if (!isset($grouped[$pid])) {
                $grouped[$pid] = [
                    'parent_id' => $row->parent_id,
                    'parent_nama' => $row->parent_nama,
                    'children' => []
                ];
            }
            $grouped[$pid]['children'][] = [
                'id' => $row->id,
                'nama_kategori' => $row->nama_kategori,
                'kode_kategori' => $row->kode_kategori,
                'total_fasilitas' => (int)$row->total_fasilitas,
            ];
        }
        // Re-index as list
        $detail_data = array_values($grouped);

		$response = [
			'status' => true,
			'data' => $detail_data
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function ajax_statistik_masyarakat()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		$total_masyarakat = $this->masyarakat_m->count_all_masyarakat();
		$masyarakat_aktif = $this->masyarakat_m->count_by_status(1);
		$masyarakat_nonaktif = $this->masyarakat_m->count_by_status(0);

		$response = [
			'status' => true,
			'data' => [
				'total_masyarakat' => $total_masyarakat,
				'masyarakat_aktif' => $masyarakat_aktif,
				'masyarakat_nonaktif' => $masyarakat_nonaktif
			]
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function ajax_data_fasilitas_map()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		$id_kecamatan = $this->input->post('id_kecamatan');
		$id_kelurahan = $this->input->post('id_kelurahan');
		$id_kota = $this->input->post('id_kota') ?: '16.73';

		// Ambil fasilitas umum yang memiliki koordinat untuk map (filtered by lokasi)
		$this->db->select('fu.*, mk.nama_kategori');
		$this->db->from('fasilitas_umum fu');
		$this->db->join('master_kategori mk', 'fu.kategori_id = mk.id', 'left');
		$this->db->where('fu.status', 1);
		$this->db->where('fu.latitude IS NOT NULL');
		$this->db->where('fu.longitude IS NOT NULL');
		$this->db->where('fu.latitude !=', '');
		$this->db->where('fu.longitude !=', '');
		$this->db->where('fu.id_kota', $id_kota);
		if (!empty($id_kecamatan)) {
			$this->db->where('fu.id_kecamatan', $id_kecamatan);
		}
		if (!empty($id_kelurahan)) {
			$this->db->where('fu.id_kelurahan', $id_kelurahan);
		}
		$this->db->order_by('fu.nama_fasilitas', 'asc');
		$fasilitas_map = $this->db->get()->result();

		$response = [
			'status' => true,
			'data' => $fasilitas_map
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	public function ajax_login_history()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		$login_history = $this->beranda_m->get_recent_logins(10);

		$response = [
			'status' => true,
			'data' => $login_history
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function ajax_data_pelaporan_map()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		$id_kecamatan = $this->input->post('id_kecamatan');
		$id_kelurahan = $this->input->post('id_kelurahan');
		$id_kota = $this->input->post('id_kota') ?: '16.73';

		// Log untuk debugging
		log_message('debug', 'Pelaporan map request - Kecamatan: ' . $id_kecamatan . ', Kelurahan: ' . $id_kelurahan . ', Kota: ' . $id_kota);

		// Ambil data pelaporan yang memiliki koordinat untuk map
		$pelaporan_map = $this->beranda_m->get_pelaporan_for_maps($id_kota, $id_kecamatan, $id_kelurahan);

		// Log hasil
		log_message('debug', 'Pelaporan map data count: ' . count($pelaporan_map));

		$response = [
			'status' => true,
			'data' => $pelaporan_map,
			'debug' => [
				'count' => count($pelaporan_map),
				'filters' => [
					'id_kota' => $id_kota,
					'id_kecamatan' => $id_kecamatan,
					'id_kelurahan' => $id_kelurahan
				]
			]
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function ajax_statistik_pelaporan()
	{
		if (!$this->session->user_login) {
			$response = ['status' => false, 'message' => 'Unauthorized access'];
			$this->output->set_content_type('application/json')->set_output(json_encode($response));
			return;
		}

		$statistik = $this->beranda_m->get_statistik_pelaporan();

		$response = [
			'status' => true,
			'data' => $statistik
		];

		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

}
