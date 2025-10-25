<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hak_akses extends MX_Controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		$user_id = $this->session->userdata('id_user');
		$this->usr = $this->user_m->user_by_id($user_id);
		ce_active_menu('admin.hak_akses.view');
	}

	public function index()
	{
		ce_hak_akses('admin.hak_akses.view');

		$data['halaman'] = 'hak_akses';
		$data['header'] = 'Hak Akses <small>Daftar Hak Akses</small>';
		$data['usr'] = $this->usr;

		$data['javascript'] = array(
			'hak_akses/js_hak_akses' => null
		);

		$this->load->view('template', $data);
	}

	public function tambah()
	{
		ce_hak_akses('admin.hak_akses.add');

		if ($this->input->method(TRUE) == 'POST') {
			$post_data['level'] = $this->input->post('level');
			$post_data['hak_akses'] = json_encode($this->input->post('hak_akses'));

			if ($this->level_m->level_insert_data($post_data)) {
				$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
				ce_set_msg('success', $success);
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
				ce_set_msg('danger', $danger);
			}

			redirect('hak-akses');
		}

		$data['hak_akses'] = $this->config->item('hak_akses');
		$data['halaman'] = 'hak_akses_tambah';
		$data['header'] = 'Hak Akses <small>Tambah Hak Akses</small>';
		$data['usr'] = $this->usr;

		$this->load->view('template', $data);
	}

	public function edit($id)
	{
		ce_hak_akses('admin.hak_akses.update');

		if ($this->input->method(TRUE) == 'POST') {
			$post_data['level'] = $this->input->post('level');
			$post_data['hak_akses'] = json_encode($this->input->post('hak_akses'));

			if ($this->level_m->level_update_data($post_data, $id)) {
				$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
				ce_set_msg('success', $success);
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
				ce_set_msg('danger', $danger);
			}

			redirect('hak-akses');
		}

		$data['hak_akses'] = $this->config->item('hak_akses');
		$data['level'] = $this->level_m->level_by_id($id);
		$data['halaman'] = 'hak_akses_edit';
		$data['header'] = 'Hak Akses <small>Edit Hak Akses</small>';
		$data['usr'] = $this->usr;

		$this->load->view('template', $data);
	}

	public function ajax_data()
	{
		ce_hak_akses('admin.hak_akses.view');

		$dataConfig = [
			'table' => 'level',
			'select' => 'id_level, level, hak_akses',
			'column_order' => [null, 'level', null],
			'column_search' => ['level'],
			'order' => ['id_level' => 'asc']
		];

		// Filter for non-super admin users (exclude level 1 if not super admin)
		$user_level = $this->session->userdata('id_level');
		if ($user_level != 1) {
			$dataConfig['condition']['id_level !='] = 1;
		}

		// Load ajax_data model for DataTable functionality
		$this->load->model('ajax_data_m');
		$this->ajax_data_m->data_config($dataConfig);
		$list = $this->ajax_data_m->get_datatables();
		$data = array();
		$no = $this->input->post('start');

		foreach ($list as $item) {
			$no++;
			$row = array();

			// No
			$row[] = $no;

			// Nama Grup
			$row[] = $item->level;

			// Aksi
			$aksi = '<div class="btn-group">';
			if (ce_hak_akses('admin.hak_akses.update')) {
				$aksi .= '<a href="'.site_url('hak-akses/edit/'.$item->id_level).'" class="btn btn-sm btn-warning" title="Edit"><i class="fa fa-edit"></i></a>';
			}
			if ($item->id_level != 1 && ce_hak_akses('admin.hak_akses.delete')) {
				$aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-id="'.$item->id_level.'" title="Hapus"><i class="fa fa-trash"></i></button>';
			}
			$aksi .= '</div>';
			$row[] = $aksi;

			$data[] = $row;
		}

		$output = array(
			"draw" => $this->input->post('draw'),
			"recordsTotal" => $this->ajax_data_m->count_all(),
			"recordsFiltered" => $this->ajax_data_m->count_filtered(),
			"data" => $data,
		);

		echo json_encode($output);
	}

	public function hapus($id)
	{
		ce_hak_akses('admin.hak_akses.delete');

		$level = $this->level_m->level_by_id($id);
		if ($level->id_level != 1) {
			$this->level_m->level_delete_data($id);
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
			ce_set_msg('success', $success);
		}

		redirect('hak-akses');
	}
}