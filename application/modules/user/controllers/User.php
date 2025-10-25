<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends MX_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('file'); 
		ce_active_menu('admin.user.view');
	}

	public function index()
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.view');
		$data['level'] = $this->level_m->level_get_all(); // Tambahkan data level untuk filter
		$data['instansi'] = $this->user_m->get_instansi_options(); // Data instansi untuk filter
		$data['halaman'] = 'user';
		$data['javascript'] = array('js/js_datatable' => array('ajax_url' => base_url('user/ajax_data'))
		);
		$data['header'] = 'User <small>Daftar User</small>';
		$this->load->view('template', $data);
	}

	public function tambah()
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.add');
		if ($this->input->method(TRUE) == 'POST') {
			$this->proses_simpan();
		}
		$data['level'] = $this->level_m->level_get_all();
		$data['instansi'] = $this->user_m->get_instansi_options();
		$data['halaman'] = 'user_form';
		$data['header'] = 'User <small>Tambah User</small>';
		$data['user'] = null;
		$this->load->view('template', $data);
	}

	public function edit($id)
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.update');
		if ($this->input->method(TRUE) == 'POST') {
			$this->proses_simpan($id);
		}
		$data['user'] = $this->user_m->user_by_id($id);
		$data['level'] = $this->level_m->level_get_all();
		$data['instansi'] = $this->user_m->get_instansi_options();
		$data['unitkerja'] = $this->user_m->get_unitkerja_options($data['user']->id_instansi ?? null);

		// Jika user tidak memiliki unitkerja yang valid untuk instansi saat ini, set null
		if ($data['user']->id_unitkerja && !empty($data['unitkerja'])) {
			$valid_unitkerja = false;
			foreach ($data['unitkerja'] as $unit) {
				if ($unit->id_unitkerja == $data['user']->id_unitkerja) {
					$valid_unitkerja = true;
					break;
				}
			}
			if (!$valid_unitkerja) {
				$data['user']->id_unitkerja = null;
			}
		}

		$data['halaman'] = 'user_form';
		$data['header'] = 'User <small>Edit User</small>';
		$this->load->view('template', $data);
	}

	private function proses_simpan($id = null)
	{
		$post_data['id_level'] = abs((int)$this->input->post('id_level'));
		$post_data['id_instansi'] = abs((int)$this->input->post('id_instansi'));
		$id_unitkerja = abs((int)$this->input->post('id_unitkerja'));
		$post_data['id_unitkerja'] = ($id_unitkerja > 0) ? $id_unitkerja : null; // Set null jika 0 atau kosong
		$post_data['username'] = htmlspecialchars($this->input->post('username'), ENT_QUOTES, 'UTF-8');
		$post_data['nama'] = htmlspecialchars($this->input->post('nama'), ENT_QUOTES, 'UTF-8');
		$post_data['no_telp'] = htmlspecialchars($this->input->post('no_telp'), ENT_QUOTES, 'UTF-8');
		$post_data['email'] = filter_var($this->input->post('email'), FILTER_SANITIZE_EMAIL);
		$post_data['alamat'] = htmlspecialchars($this->input->post('alamat'), ENT_QUOTES, 'UTF-8');
		$post_data['blokir'] = $this->input->post('blokir') ? 1 : 0;

		// Validasi duplikat username
		$this->db->where('username', $post_data['username']);
		if ($id) {
			$this->db->where('id_user !=', $id);
		}
		$username_exists = $this->db->get('user')->num_rows() > 0;

		if ($username_exists) {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Username "' . $post_data['username'] . '" sudah digunakan oleh user lain.';
			ce_set_msg('danger', $danger);
			redirect($id ? 'user/edit/' . $id : 'user/tambah');
			exit;
		}

		if ($this->input->post('password')) {
			$post_data['password'] = password_hash($this->input->post('password'), PASSWORD_DEFAULT);
		}

		if (!empty($_FILES['foto']['tmp_name'])) {
			$clean_username = preg_replace('/[^a-zA-Z0-9_]/', '', $post_data['username']);
			$clean_nama = preg_replace('/[^a-zA-Z0-9_]/', '', $post_data['nama']);
			$user_dir = "uploads/filemanager/{$clean_username}_{$clean_nama}/";
			if (!is_dir(FCPATH . $user_dir)) {
				mkdir(FCPATH . $user_dir, 0755, TRUE);
			}
			$config['upload_path'] = FCPATH . $user_dir;
			$config['allowed_types'] = 'jpg|png|jpeg';
			$config['file_name'] = 'user_' . time() . '_' . substr(md5($post_data['username']), 0, 8);
			$config['max_size'] = 2048;
			$this->load->library('upload', $config);
			$this->upload->initialize($config);
			if (!$this->upload->do_upload('foto')) {
				$error = $this->upload->display_errors('', '');
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> ' . $error;
				ce_set_msg('danger', $danger);
				redirect($id ? 'user/edit/' . $id : 'user/tambah');
				exit;
			} else {
				$post_data['foto'] = $user_dir . $this->upload->data('file_name');
			}
		}

		if ($id) {
			$result = $this->user_m->user_update_data($post_data, $id);
		} else {
			$result = $this->user_m->user_insert_data($post_data);
		}

		if ($result) {
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data user telah tersimpan.';
			ce_set_msg('success', $success);
		} else {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data user gagal tersimpan.';
			ce_set_msg('danger', $danger);
		}
		redirect('user');
	}

	public function hapus($id)
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.delete');
		$user = $this->user_m->user_by_id($id);
		if ($user->id_level != 1) {
			if (!empty($user->foto) && file_exists(FCPATH . $user->foto)) {
				unlink(FCPATH . $user->foto);
			}
			$this->user_m->user_delete_data($id);
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
			ce_set_msg('success', $success);
		}
		redirect('user');
	}

	public function bulk_delete()
	{
		if (!$this->session->user_login) {
			$response = [
				'status' => false,
				'message' => 'Session expired'
			];
			header('Content-Type: application/json');
			echo json_encode($response);
			exit;
		}

		ce_hak_akses('admin.user.delete');

		$ids = $this->input->post('ids');
		if (empty($ids) || !is_array($ids)) {
			$response = [
				'status' => false,
				'message' => 'Tidak ada data yang dipilih'
			];
		} else {
			$deleted_count = 0;
			$skipped_count = 0;
			$errors = [];

			foreach ($ids as $id) {
				$user = $this->user_m->user_by_id($id);
				if (!$user) {
					$errors[] = "User ID {$id} tidak ditemukan";
					continue;
				}

				// Skip user dengan id_user = 1 (super admin)
				if ($user->id_level == 1 && $id == 1) {
					$skipped_count++;
					continue;
				}

				// Hapus foto jika ada
				if (!empty($user->foto) && file_exists(FCPATH . $user->foto)) {
					unlink(FCPATH . $user->foto);
				}

				// Hapus user
				if ($this->user_m->user_delete_data($id)) {
					$deleted_count++;
				} else {
					$errors[] = "Gagal menghapus user ID {$id}";
				}
			}

			$message = '';
			if ($deleted_count > 0) {
				$message .= "Berhasil menghapus {$deleted_count} user";
			}
			if ($skipped_count > 0) {
				if ($message) $message .= '. ';
				$message .= "{$skipped_count} user (super admin) dilewati";
			}
			if (!empty($errors)) {
				if ($message) $message .= '. ';
				$message .= 'Error: ' . implode(', ', $errors);
			}

			$response = [
				'status' => $deleted_count > 0,
				'message' => $message,
				'deleted' => $deleted_count,
				'skipped' => $skipped_count
			];
		}

		header('Content-Type: application/json');
		echo json_encode($response);
	}

	public function ajax_data()
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.view');

		// Gunakan query manual untuk memastikan join bekerja dengan benar
		$this->db->select('
			user.*,
			level.level,
			tbInstansi.instansi_nama,
			tbUnitKerja.unitkerja
		');
		$this->db->from('user');
		$this->db->join('level', 'user.id_level = level.id_level', 'left');
		$this->db->join('tbInstansi', 'user.id_instansi = tbInstansi.id_instansi', 'left');
		$this->db->join('tbUnitKerja', 'user.id_unitkerja = tbUnitKerja.id_unitkerja', 'left');

		// Handle filters
		$status_aktif = $this->input->post('status_aktif');
		$filter_level = $this->input->post('filter_level');
		$filter_instansi = $this->input->post('filter_instansi');
		$filter_unitkerja = $this->input->post('filter_unitkerja');

		if ($status_aktif !== '' && $status_aktif !== null) {
			$this->db->where('user.blokir', $status_aktif);
		}
		if ($filter_level !== '' && $filter_level !== null) {
			$this->db->where('user.id_level', $filter_level);
		}
		if ($filter_instansi !== '' && $filter_instansi !== null) {
			$this->db->where('user.id_instansi', $filter_instansi);
		}
		if ($filter_unitkerja !== '' && $filter_unitkerja !== null) {
			$this->db->where('user.id_unitkerja', $filter_unitkerja);
		}

		// Handle search
		$search = $this->input->post('search');
		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('user.nama', $search['value']);
			$this->db->or_like('user.username', $search['value']);
			$this->db->or_like('user.email', $search['value']);
			$this->db->or_like('user.no_telp', $search['value']);
			$this->db->or_like('tbInstansi.instansi_nama', $search['value']);
			$this->db->or_like('tbUnitKerja.unitkerja', $search['value']);
			$this->db->or_like('level.level', $search['value']);
			$this->db->group_end();
		}

		// Handle ordering
		$order = $this->input->post('order');
		if (!empty($order)) {
			$column_index = $order[0]['column'];
			$direction = $order[0]['dir'];

			// Map column index to actual column
			$columns = array('', 'user.id_user', '', 'user.nama', 'user.username', 'tbInstansi.instansi_nama', 'tbUnitKerja.unitkerja', 'level.level', '', '');
			if (isset($columns[$column_index]) && !empty($columns[$column_index])) {
				$this->db->order_by($columns[$column_index], $direction);
			}
		} else {
			$this->db->order_by('user.id_user', 'desc');
		}

		// Clone query for counting
		$count_query = clone $this->db;
		$total_records = $count_query->count_all_results('', false);

		// Handle pagination
		$start = $this->input->post('start');
		$length = $this->input->post('length');
		if ($length != -1) {
			$this->db->limit($length, $start);
		}

		$query = $this->db->get();
		$list = $query->result();

		$data = array();
		$no = $start + 1;
		foreach ($list as $field) {
			$row = array();

			// Checkbox - disable untuk user id=1
			$checkbox = '<input type="checkbox" class="form-check-input row-checkbox" value="' . $field->id_user . '"';
			if ($field->id_level == 1 && $field->id_user == 1) {
				$checkbox .= ' disabled';
			}
			$checkbox .= '>';
			$row[] = $checkbox;

			$row[] = $no;
			$foto_url = base_url('user/foto/' . $field->id_user);
			$row[] = '<img src="' . $foto_url . '" class="user-avatar" alt="avatar">';
			$row[] = $field->nama;
			$row[] = $field->username;
			$row[] = $field->instansi_nama ? $field->instansi_nama : '<span class="text-muted">-</span>';
			$row[] = $field->unitkerja ? $field->unitkerja : '<span class="text-muted">-</span>';
			$row[] = $field->level ? $field->level : '<span class="text-muted">-</span>';

			// Status dengan label sederhana
			$status_label = $field->blokir == 0 ?
				'<span class="label label-success">Aktif</span>' :
				'<span class="label label-danger">Diblokir</span>';
			$row[] = $status_label;

			$aksi = '<div class="btn-group">
						<a href="' . base_url('user/edit/' . $field->id_user) . '" class="btn btn-primary btn-sm" title="Edit">
							<i class="fa fa-edit"></i>
						</a>';
			if ($field->id_level != 1) {
				$aksi .= '<a href="' . base_url('user/hapus/' . $field->id_user) . '" class="btn btn-danger btn-sm" title="Hapus" onclick="return delete_confirm();">
							<i class="fa fa-trash"></i>
						</a>';
			}
			if ($field->blokir == 1) {
				$aksi .= '<a href="' . base_url('user/aktivasi/' . $field->id_user) . '" class="btn btn-success btn-sm" title="Aktifkan" onclick="return confirm(\'Aktifkan user ini?\');">
							<i class="fa fa-check-circle"></i>
						</a>';
			} else if ($field->id_level != 1) {
				$aksi .= '<a href="' . base_url('user/blokir/' . $field->id_user) . '" class="btn btn-warning btn-sm" title="Blokir" onclick="return confirm(\'Blokir user ini?\');">
							<i class="fa fa-ban"></i>
						</a>';
			}
			$aksi .= '</div>';
			$row[] = $aksi;
			$data[] = $row;
			$no++;
		}

		// Clone query for filtered count
		$this->db->select('COUNT(*) as count');
		$this->db->from('user');
		$this->db->join('level', 'user.id_level = level.id_level', 'left');
		$this->db->join('tbInstansi', 'user.id_instansi = tbInstansi.id_instansi', 'left');
		$this->db->join('tbUnitKerja', 'user.id_unitkerja = tbUnitKerja.id_unitkerja', 'left');

		// Re-apply filters for filtered count
		if ($status_aktif !== '' && $status_aktif !== null) {
			$this->db->where('user.blokir', $status_aktif);
		}
		if ($filter_level !== '' && $filter_level !== null) {
			$this->db->where('user.id_level', $filter_level);
		}
		if ($filter_instansi !== '' && $filter_instansi !== null) {
			$this->db->where('user.id_instansi', $filter_instansi);
		}
		if ($filter_unitkerja !== '' && $filter_unitkerja !== null) {
			$this->db->where('user.id_unitkerja', $filter_unitkerja);
		}

		if (!empty($search['value'])) {
			$this->db->group_start();
			$this->db->like('user.nama', $search['value']);
			$this->db->or_like('user.username', $search['value']);
			$this->db->or_like('user.email', $search['value']);
			$this->db->or_like('user.no_telp', $search['value']);
			$this->db->or_like('tbInstansi.instansi_nama', $search['value']);
			$this->db->or_like('tbUnitKerja.unitkerja', $search['value']);
			$this->db->or_like('level.level', $search['value']);
			$this->db->group_end();
		}

		$filtered_query = $this->db->get();
		$filtered_records = $filtered_query->row()->count;

		$output = array(
			"draw" => intval($this->input->post('draw')),
			"recordsTotal" => $total_records,
			"recordsFiltered" => $filtered_records,
			"data" => $data
		);

		echo json_encode($output);
	}

	public function foto($id)
	{
		if (!$this->session->userdata('user_login')) {
			$file_path = FCPATH . 'assets/img/user/default.png';
		} else {
			$user = $this->user_m->user_by_id($id);
			$file_path = FCPATH . ($user->foto ?? 'assets/img/user/default.png');
			if (empty($user->foto) || !file_exists($file_path)) {
				$file_path = FCPATH . 'assets/img/user/default.png';
			}
		}
		$mime = get_mime_by_extension($file_path);
		if ($mime === FALSE) {
			$mime = 'application/octet-stream';
		}
		header('Content-Type: ' . $mime);
		header('Content-Length: ' . filesize($file_path));
		header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
		header('Cache-Control: public, max-age=31536000');
		header('Pragma: cache');
		header('Expires: ' . gmdate(DATE_RFC1123, time() + 31536000));
		readfile($file_path);
		exit;
	}

	public function aktivasi($id)
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.update');
		$user = $this->user_m->user_by_id($id);
		if (!$user) {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> User tidak ditemukan.';
			ce_set_msg('danger', $danger);
			redirect('user');
			exit;
		}
		$update_data = ['blokir' => 0];
		if ($this->user_m->user_update_data($update_data, $id)) {
			$wa_data = [
				'no_telp' => $user->no_telp,
				'username' => $user->username,
				'nama' => $user->nama
			];
			$result = $this->send_activation_notification($wa_data);
			$status_wa = isset($result['status']) && $result['status'] ? 'berhasil' : 'gagal';
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> User berhasil diaktifkan dan notifikasi WhatsApp ' . $status_wa . ' dikirim.';
			if ($status_wa == 'gagal' && isset($result['message'])) {
				$success .= '<br>Detail: ' . $result['message'];
			}
			ce_set_msg('success', $success);
		} else {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Gagal mengaktifkan user.';
			ce_set_msg('danger', $danger);
		}
		redirect('user');
	}

	public function blokir($id)
	{
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		ce_hak_akses('admin.user.update');
		$user = $this->user_m->user_by_id($id);
		if (!$user) {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> User tidak ditemukan.';
			ce_set_msg('danger', $danger);
			redirect('user');
			exit;
		}
		if ($user->id_level == 1) {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Tidak dapat memblokir akun administrator.';
			ce_set_msg('danger', $danger);
			redirect('user');
			exit;
		}
		$update_data = ['blokir' => 1];
		if ($this->user_m->user_update_data($update_data, $id)) {
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> User berhasil diblokir.';
			ce_set_msg('success', $success);
		} else {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Gagal memblokir user.';
			ce_set_msg('danger', $danger);
		}
		redirect('user');
	}

	public function login()
	{
		if ($this->session->userdata('user_login')) {
			redirect('beranda');
			exit;
		}
		$this->load->library('user_agent');
		$ip_address = $this->input->ip_address();
		$user_agent = $this->agent->agent_string();
		$failed_attempts = $this->Log_login_m->get_login_attempts($ip_address);
		if ($failed_attempts >= 5) {
			$danger = '<h4><i class="icon fa fa-ban"></i>Terlalu Banyak Percobaan!</h4> Akun Anda diblokir selama 15 menit.';
			ce_set_msg('danger', $danger);
			if ($this->input->is_ajax_request()) {
				$response = array('success' => false, 'message' => $danger);
				echo json_encode($response);
				exit;
			}
			redirect('user/login');
			exit;
		}
		if ($this->input->method(TRUE) == 'POST') {
			$is_ajax = $this->input->is_ajax_request();
			$username = html_escape($this->input->post('username'));
			$password = html_escape($this->input->post('password'));
			$log_data = array('ip_address' => $ip_address, 'user_agent' => $user_agent, 'username' => $username, 'attempt_time' => date('Y-m-d H:i:s'), 'status' => 'pending');
			if (empty($username) || empty($password)) {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Silahkan isi nama pengguna dan kata sandi.';
				ce_set_msg('danger', $danger);
				$log_data['status'] = 'failed';
				$log_data['message'] = 'Kredensial kosong';
				$this->Log_login_m->insert_log($log_data);
				if ($is_ajax) {
					$response = array('success' => false, 'message' => $danger);
					echo json_encode($response);
					exit;
				}
				redirect('user/login');
			}
			$user = $this->user_m->user_cek_login($username);
			if ($user) {
				if (!password_verify($password, $user->password)) {
					$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Nama pengguna atau kata sandi yang Anda masukan salah.';
					ce_set_msg('danger', $danger);
					$log_data['status'] = 'failed';
					$log_data['message'] = 'Kata sandi salah';
					$this->Log_login_m->insert_log($log_data);
					if ($is_ajax) {
						$response = array('success' => false, 'message' => $danger);
						echo json_encode($response);
						exit;
					}
					redirect('user/login');
				}
				if (isset($user->blokir) && $user->blokir == 1) {
					$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Akun Anda telah diblokir. Silahkan hubungi administrator.';
					ce_set_msg('danger', $danger);
					$log_data['status'] = 'blocked';
					$log_data['message'] = 'Akun diblokir';
					$this->Log_login_m->insert_log($log_data);
					if ($is_ajax) {
						$response = array('success' => false, 'message' => $danger);
						echo json_encode($response);
						exit;
					}
					redirect('user/login');
				}
				$this->session->sess_regenerate(TRUE);
				$session_data = array(
					'user_login' => true,
					'id_user' => $user->id_user,
					'id_level' => $user->id_level,
					'id_unitkerja' => $user->id_unitkerja,
					'tanggal_login' => time(),
					'ip_address' => $ip_address,
					'user_agent' => $user_agent,
					'last_activity' => time()
				);
				$this->session->set_userdata($session_data);
				$update_data = array('last_login' => date('Y-m-d H:i:s'), 'last_ip' => $ip_address);
				$this->user_m->user_update_data($update_data, $user->id_user);
				$log_data['status'] = 'success';
				$log_data['message'] = 'Login berhasil';
				$log_data['id_user'] = $user->id_user;
				$this->Log_login_m->insert_log($log_data);
				if ($is_ajax) {
					$response = array('success' => true, 'message' => 'Login berhasil', 'redirect' => base_url('beranda'));
					echo json_encode($response);
					exit;
				}
				redirect('beranda');
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Nama pengguna atau kata sandi yang Anda masukan salah.';
				ce_set_msg('danger', $danger);
				$log_data['status'] = 'failed';
				$log_data['message'] = 'Nama pengguna tidak valid';
				$this->Log_login_m->insert_log($log_data);
				if ($is_ajax) {
					$response = array('success' => false, 'message' => $danger);
					echo json_encode($response);
					exit;
				}
				redirect('user/login');
			}
		}
		$this->Log_login_m->clean_old_logs();
		$this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$this->output->set_header('Pragma: no-cache');
		$this->load->view('login');
	}

	public function logout()
	{
		if ($this->session->userdata('user_login')) {
			$log_data = array('ip_address' => $this->input->ip_address(), 'user_agent' => $this->agent->agent_string(), 'username' => $this->session->userdata('username'), 'id_user' => $this->session->userdata('id_user'), 'attempt_time' => date('Y-m-d H:i:s'), 'status' => 'logout', 'message' => 'Pengguna keluar');
			$this->Log_login_m->insert_log($log_data);
		}
		$this->session->sess_destroy();
		redirect('user/login');
	}

	public function get_unitkerja_options()
	{
		$instansi_id = $this->input->post('instansi_id');
		$options = $this->user_m->get_unitkerja_options($instansi_id);
		$response = [
			'status' => true,
			'data' => $options
		];
		header('Content-Type: application/json');
		echo json_encode($response);
	}

	public function check_username()
	{
		$username = $this->input->post('username');
		$id_user = $this->input->post('id_user'); // untuk edit user

		if (empty($username)) {
			$response = [
				'status' => 'empty',
				'message' => 'Username tidak boleh kosong'
			];
		} else {
			// Cek apakah username sudah ada
			$this->db->where('username', $username);
			if ($id_user) {
				$this->db->where('id_user !=', $id_user);
			}
			$exists = $this->db->get('user')->num_rows() > 0;

			if ($exists) {
				$response = [
					'status' => 'taken',
					'message' => 'Username sudah digunakan'
				];
			} else {
				$response = [
					'status' => 'available',
					'message' => 'Username tersedia'
				];
			}
		}

		header('Content-Type: application/json');
		echo json_encode($response);
	}

	private function send_activation_notification($data)
	{
		$api_config = ['waapi_url' => ce_opsi('waapi_url'), 'waapi_key' => ce_opsi('waapi_key'), 'waapi_sender' => ce_opsi('waapi_sender')];
		$this->db->insert('log_notifikasi', ['waktu' => date('Y-m-d H:i:s'), 'tipe' => 'debug_config', 'nomor_tujuan' => $data['no_telp'], 'pesan' => 'Debug konfigurasi API WhatsApp', 'status' => 'info', 'response' => json_encode($api_config)]);
		$template = $this->db->get_where('wa_template', ['kode' => 'account_activation'])->row();
		if (!$template) {
			$template_data = ['nama' => 'Notifikasi Aktivasi Akun', 'kode' => 'account_activation', 'isi_template' => "Halo {nama},\n\nSelamat! Akun Anda dengan NIP {username} telah diaktifkan oleh administrator.\n\nAnda sekarang dapat login ke sistem " . ce_opsi('nama_situs') . " menggunakan NIP dan password yang telah didaftarkan.\n\nTerima kasih.", 'deskripsi' => 'Template notifikasi WhatsApp untuk aktivasi akun', 'created_at' => date('Y-m-d H:i:s'), 'created_by' => 1];
			$this->db->insert('wa_template', $template_data);
			$template = (object) $template_data;
		}
		$nomor_hp = $data['no_telp'];
		if (substr($nomor_hp, 0, 1) == '0') {
			$nomor_hp = '62' . substr($nomor_hp, 1);
		} elseif (substr($nomor_hp, 0, 2) != '62') {
			$nomor_hp = '62' . $nomor_hp;
		}
		$params = ['username' => $data['username'], 'nama' => $data['nama']];
		$pesan_template = $template->isi_template;
		foreach ($params as $key => $value) {
			$pesan_template = str_replace('{' . $key . '}', $value, $pesan_template);
		}
		$this->db->insert('log_notifikasi', ['waktu' => date('Y-m-d H:i:s'), 'tipe' => 'debug_message', 'nomor_tujuan' => $nomor_hp, 'pesan' => $pesan_template, 'status' => 'info', 'response' => json_encode($params)]);
		$result = kirim_template($nomor_hp, 'account_activation', $params, null, ['nama_tujuan' => 'Aktivasi Akun - ' . $data['username'], 'created_by' => $this->session->userdata('id_user') ?? 1]);
		$log_data = ['waktu' => date('Y-m-d H:i:s'), 'tipe' => 'aktivasi_akun', 'nomor_tujuan' => $nomor_hp, 'pesan' => 'Aktivasi akun untuk ' . $data['username'] . ' (' . $data['nama'] . ')', 'status' => isset($result['status']) && $result['status'] ? 'success' : 'failed', 'response' => json_encode($result)];
		$this->db->insert('log_notifikasi', $log_data);
		return $result;
	}
}
