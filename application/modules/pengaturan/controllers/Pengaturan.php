<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pengaturan extends MX_Controller
{

	public function __construct()
	{
		parent::__construct();
		if (!$this->session->user_login) {
			redirect('user/login');
			exit;
		}
		// Pastikan tidak ada output sebelum header
		ob_start();
		// Disable CSRF protection for this module
		$this->config->set_item('csrf_protection', FALSE);
	}

	public function index()
	{
		show_404();
	}

	public function aplikasi()
	{
		ce_hak_akses('admin.pengaturan.aplikasi');
		ce_active_menu('admin.pengaturan.aplikasi');

		if ($this->input->method(TRUE) == 'POST') {
			$post_data['nama_situs'] = $this->input->post('nama_situs');
            $post_data['tagline'] = $this->input->post('tagline');
            $post_data['meta_description'] = $this->input->post('meta_description');
            $post_data['meta_keywords'] = $this->input->post('meta_keywords');
            $post_data['nomor_kontak'] = $this->input->post('nomor_kontak');
            $post_data['email'] = $this->input->post('email');
            $post_data['alamat'] = $this->input->post('alamat');
            

			if (!empty($_FILES['logo']['tmp_name'])) {
				$upload_path = 'uploads/pengaturan/';
				
				// Pastikan direktori ada dan dapat ditulis
				if (!is_dir($upload_path) || !is_writable($upload_path)) {
					ce_set_msg('danger', '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Direktori upload tidak valid atau tidak dapat ditulis.');
				} else {
					$config['upload_path']          = $upload_path;
					$config['allowed_types']        = 'jpg|png|gif';
					$config['file_name']            = 'logo_' . time();
					$config['overwrite']            = TRUE;

					$this->load->library('upload', $config);
					$this->upload->initialize($config);

					if ($this->upload->do_upload('logo')) {
						$post_data['logo'] = $upload_path . $config['file_name'] . $this->upload->data('file_ext');
						$old_logo = ce_opsi('logo');
						if(!empty($old_logo) && file_exists($old_logo)) {
							@unlink($old_logo);
						}
					} else {
						$error = $this->upload->display_errors('', '');
						ce_set_msg('danger', '<h4><i class="icon fa fa-ban"></i>Ups!</h4> ' . $error);
					}
				}
			}

			if (!empty($_FILES['favicon']['tmp_name'])) {
				$upload_path = 'uploads/pengaturan/';
				
				// Pastikan direktori ada dan dapat ditulis
				if (!is_dir($upload_path) || !is_writable($upload_path)) {
					ce_set_msg('danger', '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Direktori upload tidak valid atau tidak dapat ditulis.');
				} else {
					$config['upload_path']          = $upload_path;
					$config['allowed_types']        = 'jpg|png|gif';
					$config['file_name']            = 'favicon_' . time();
					$config['overwrite']            = TRUE;

					$this->load->library('upload', $config);
					$this->upload->initialize($config);

					if ($this->upload->do_upload('favicon')) {
						$post_data['favicon'] = $upload_path . $config['file_name'] . $this->upload->data('file_ext');
						$old_favicon = ce_opsi('favicon');
						if(!empty($old_favicon) && file_exists($old_favicon)) {
							@unlink($old_favicon);
						}
					} else {
						$error = $this->upload->display_errors('', '');
						ce_set_msg('danger', '<h4><i class="icon fa fa-ban"></i>Ups!</h4> ' . $error);
					}
				}
			}

			if (ce_set_opsi($post_data)) {
				$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
				ce_set_msg('success', $success);
			} else {
				$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
				ce_set_msg('danger', $danger);
			}

			redirect('pengaturan/aplikasi');
		}

		$data['active_tab'] = 'aplikasi';
		$data['halaman'] = 'pengaturan_index';
		$data['header'] = 'Pengaturan <small>Pengaturan Aplikasi</small>';

		$this->load->view('template', $data);
	}
    
    /**
     * Method untuk update nama situs via AJAX
     */
    public function update_nama_situs()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');
        
        $nama_situs = $this->input->post('nama_situs');
        
        if (empty($nama_situs)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Nama situs tidak boleh kosong'
            ]);
            exit;
        }
        
        $post_data['nama_situs'] = $nama_situs;
        
        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Nama situs berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui nama situs'
            ]);
        }
        exit;
    }
    
    /**
     * Method untuk upload logo via AJAX
     */
    public function upload_logo()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');
        
        $upload_path = 'uploads/pengaturan/';
        
        // Pastikan direktori ada dan dapat ditulis
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        if (!is_writable($upload_path)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Direktori upload tidak dapat ditulis'
            ]);
            exit;
        }
        
        $config['upload_path']      = $upload_path;
        $config['allowed_types']    = 'jpg|jpeg|png|gif';
        $config['file_name']        = 'logo_' . time();
        $config['max_size']         = 2048; // 2MB
        $config['overwrite']        = TRUE;
        
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('logo')) {
            echo json_encode([
                'status' => 'error',
                'message' => $this->upload->display_errors('', '')
            ]);
            exit;
        }
        
        $upload_data = $this->upload->data();
        $file_path = $upload_path . $upload_data['file_name'];
        
        // Simpan path file ke database
        $post_data['logo'] = $file_path;
        
        // Hapus file logo lama jika ada
        $old_logo = ce_opsi('logo');
        if (!empty($old_logo) && file_exists($old_logo) && $old_logo != $file_path) {
            @unlink($old_logo);
        }
        
        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Logo berhasil diupload',
                'file_url' => base_url($file_path)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menyimpan data logo'
            ]);
        }
        exit;
    }
    
    public function upload_favicon()
    {
        ob_clean();
        
        ce_hak_akses('admin.pengaturan.aplikasi');
        
        $upload_path = 'uploads/pengaturan/';
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        if (!is_writable($upload_path)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Direktori upload tidak dapat ditulis'
            ]);
            exit;
        }
        
        $config['upload_path']      = $upload_path;
        $config['allowed_types']    = 'jpg|jpeg|png|gif';
        $config['file_name']        = 'favicon_' . time();
        $config['max_size']         = 2048; // 2MB
        $config['overwrite']        = TRUE;
        
        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        
        if (!$this->upload->do_upload('favicon')) {
            echo json_encode([
                'status' => 'error',
                'message' => $this->upload->display_errors('', '')
            ]);
            exit;
        }
        
        $upload_data = $this->upload->data();
        $file_path = $upload_path . $upload_data['file_name'];
        
        // Simpan path file ke database
        $post_data['favicon'] = $file_path;
        
        // Hapus file favicon lama jika ada
        $old_favicon = ce_opsi('favicon');
        if (!empty($old_favicon) && file_exists($old_favicon) && $old_favicon != $file_path) {
            @unlink($old_favicon);
        }
        
        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Favicon berhasil diupload',
                'file_url' => base_url($file_path)
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal menyimpan data favicon'
            ]);
        }
        exit;
    }

    /**
     * Method untuk update tagline via AJAX
     */
    public function update_tagline()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');

        $tagline = $this->input->post('tagline');

        $post_data['tagline'] = $tagline;

        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Tagline berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui tagline'
            ]);
        }
        exit;
    }

    /**
     * Method untuk update meta description via AJAX
     */
    public function update_meta_description()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');

        $meta_description = $this->input->post('meta_description');

        $post_data['meta_description'] = $meta_description;

        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Meta description berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui meta description'
            ]);
        }
        exit;
    }

    /**
     * Method untuk update meta keywords via AJAX
     */
    public function update_meta_keywords()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');

        $meta_keywords = $this->input->post('meta_keywords');

        $post_data['meta_keywords'] = $meta_keywords;

        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Meta keywords berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui meta keywords'
            ]);
        }
        exit;
    }

    /**
     * Method untuk update nomor kontak via AJAX
     */
    public function update_nomor_kontak()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');

        $nomor_kontak = $this->input->post('nomor_kontak');

        $post_data['nomor_kontak'] = $nomor_kontak;

        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Nomor kontak berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui nomor kontak'
            ]);
        }
        exit;
    }

    /**
     * Method untuk update email via AJAX
     */
    public function update_email()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');

        $email = $this->input->post('email');

        // Validasi email jika tidak kosong
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Format email tidak valid'
            ]);
            exit;
        }

        $post_data['email'] = $email;

        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Email berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui email'
            ]);
        }
        exit;
    }

    /**
     * Method untuk update alamat via AJAX
     */
    public function update_alamat()
    {
        // Bersihkan output buffer
        ob_clean();
        
        // Cek hak akses
        ce_hak_akses('admin.pengaturan.aplikasi');

        $alamat = $this->input->post('alamat');

        $post_data['alamat'] = $alamat;

        if (ce_set_opsi($post_data)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Alamat berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Gagal memperbarui alamat'
            ]);
        }
        exit;
    }

	public function api()
	{
	    ce_hak_akses('admin.pengaturan.api');

$this->load->library('form_validation');
$this->form_validation->set_rules('gemini_api_key', 'Gemini API Key', 'required');
$this->form_validation->set_rules('waapi_url', 'WA API URL', 'required');

	    if ($this->form_validation->run() == TRUE) {
	        $post_data = array();
	        $post_data['gemini_api_key'] = $this->input->post('gemini_api_key');
	        $post_data['gemini_model'] = $this->input->post('gemini_model');
	        $post_data['gemini_temperature'] = $this->input->post('gemini_temperature');
	        $post_data['gemini_max_tokens'] = $this->input->post('gemini_max_tokens');
	        $post_data['waapi_url'] = $this->input->post('waapi_url');
	        $post_data['waapi_key'] = $this->input->post('waapi_key');
	        $post_data['waapi_sender'] = $this->input->post('waapi_sender');
	        $post_data['pegawai_api_url'] = $this->input->post('pegawai_api_url');
	        $post_data['pegawai_api_header'] = $this->input->post('pegawai_api_header');
	        $post_data['pegawai_api_key'] = $this->input->post('pegawai_api_key');

	        // Save all settings
	        ce_set_opsi($post_data);
	        ce_set_msg('success', 'Pengaturan API berhasil disimpan');
	        redirect('pengaturan/api');
	        return;
	    }

	    $data['active_tab'] = 'api';
	    $data['header'] = 'Pengaturan <small>API</small>';
	    $data['halaman'] = 'pengaturan_index';
	    $this->load->view('template', $data);
	}

	public function api_internal()
	{
	    ce_hak_akses('admin.pengaturan.api_internal');

	    // Handle AJAX for domain and IP management
	    if ($this->input->is_ajax_request()) {
	        $this->handle_api_management_ajax();
	        return;
	    }

	    $this->load->library('form_validation');
	    $this->form_validation->set_rules('api_enabled', 'API Enabled', 'required');
	    $this->form_validation->set_rules('base_url', 'Base URL', 'required');
	    $this->form_validation->set_rules('rate_limit_per_minute', 'Rate Limit Per Minute', 'required|numeric');
	    $this->form_validation->set_rules('rate_limit_per_hour', 'Rate Limit Per Hour', 'required|numeric');

	    if ($this->form_validation->run() == TRUE) {
	        // Update API settings
	        $settings_data = array(
	            'api_enabled' => $this->input->post('api_enabled'),
	            'base_url' => $this->input->post('base_url'),
	            'api_key' => $this->input->post('api_key'),
	            'jwt_secret' => $this->input->post('jwt_secret'),
	            'rate_limit_per_minute' => $this->input->post('rate_limit_per_minute'),
	            'rate_limit_per_hour' => $this->input->post('rate_limit_per_hour')
	        );

	        // Save settings to api_settings table
	        $this->load->model('Pengaturan_m');
	        $this->Pengaturan_m->update_api_settings($settings_data);

	        ce_set_msg('success', 'Pengaturan API Internal berhasil disimpan');
	        redirect('pengaturan/api_internal');
	        return;
	    }

	    $data['active_tab'] = 'api_internal';
	    $data['header'] = 'Pengaturan <small>API Internal</small>';
	    $data['halaman'] = 'pengaturan_index';
	    $this->load->view('template', $data);
	}

	/**
	 * API Monitoring Dashboard
	 */
	public function api_monitoring()
	{
	    ce_hak_akses('admin.pengaturan.api_internal');

	    // Get API monitoring data
	    $this->load->model('Pengaturan_m');

	    // Recent API logs (last 100)
	    $data['recent_logs'] = $this->Pengaturan_m->get_recent_api_logs(100);

	    // API usage statistics (last 7 days)
	    $data['usage_stats'] = $this->Pengaturan_m->get_api_usage_stats(7);

	    // Top endpoints by usage
	    $data['top_endpoints'] = $this->Pengaturan_m->get_top_endpoints(10);

	    // Error rate statistics
	    $data['error_stats'] = $this->Pengaturan_m->get_api_error_stats(7);

	    // Rate limit violations
	    $data['rate_limit_violations'] = $this->Pengaturan_m->get_rate_limit_violations(24);

	    $data['active_tab'] = 'api_monitoring';
	    $data['header'] = 'Monitoring <small>API Internal</small>';
	    $data['halaman'] = 'pengaturan_api_monitoring';
	    $this->load->view('template', $data);
	}

	/**
	 * Handle AJAX requests for API management
	 */
	private function handle_api_management_ajax()
	{
	    $action = $this->input->post('action');

	    switch ($action) {
	        case 'add_domain':
	            $this->add_domain_ajax();
	            break;
	        case 'update_domain':
	            $this->update_domain_ajax();
	            break;
	        case 'delete_domain':
	            $this->delete_domain_ajax();
	            break;
	        case 'add_ip':
	            $this->add_ip_ajax();
	            break;
	        case 'update_ip':
	            $this->update_ip_ajax();
	            break;
	        case 'delete_ip':
	            $this->delete_ip_ajax();
	            break;
	        case 'get_domains':
	            $this->get_domains_ajax();
	            break;
	        case 'get_ips':
	            $this->get_ips_ajax();
	            break;
	        case 'get_metrics':
	            $this->get_metrics_ajax();
	            break;
	        case 'get_usage_chart':
	            $this->get_usage_chart_ajax();
	            break;
	        case 'get_top_endpoints':
	            $this->get_top_endpoints_ajax();
	            break;
	        default:
	            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
	            break;
	    }
	    exit;
	}

	private function add_domain_ajax()
	{
	    $domain = $this->input->post('domain');
	    $description = $this->input->post('description');

	    if (empty($domain)) {
	        echo json_encode(['status' => 'error', 'message' => 'Domain tidak boleh kosong']);
	        return;
	    }

	    $this->load->model('Pengaturan_m');
	    $result = $this->Pengaturan_m->add_api_domain([
	        'domain' => $domain,
	        'description' => $description
	    ]);

	    if ($result) {
	        echo json_encode(['status' => 'success', 'message' => 'Domain berhasil ditambahkan']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan domain']);
	    }
	}

	private function update_domain_ajax()
	{
	    $id = $this->input->post('id');
	    $domain = $this->input->post('domain');
	    $description = $this->input->post('description');
	    $is_active = $this->input->post('is_active');

	    $this->load->model('Pengaturan_m');
	    $result = $this->Pengaturan_m->update_api_domain($id, [
	        'domain' => $domain,
	        'description' => $description,
	        'is_active' => $is_active
	    ]);

	    if ($result) {
	        echo json_encode(['status' => 'success', 'message' => 'Domain berhasil diperbarui']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui domain']);
	    }
	}

	private function delete_domain_ajax()
	{
	    $id = $this->input->post('id');

	    $this->load->model('Pengaturan_m');
	    $result = $this->Pengaturan_m->delete_api_domain($id);

	    if ($result) {
	        echo json_encode(['status' => 'success', 'message' => 'Domain berhasil dihapus']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus domain']);
	    }
	}

	private function add_ip_ajax()
	{
	    $ip_address = $this->input->post('ip_address');
	    $description = $this->input->post('description');

	    if (empty($ip_address)) {
	        echo json_encode(['status' => 'error', 'message' => 'IP Address tidak boleh kosong']);
	        return;
	    }

	    $this->load->model('Pengaturan_m');
	    $result = $this->Pengaturan_m->add_api_ip([
	        'ip_address' => $ip_address,
	        'description' => $description
	    ]);

	    if ($result) {
	        echo json_encode(['status' => 'success', 'message' => 'IP berhasil ditambahkan']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Gagal menambahkan IP']);
	    }
	}

	private function update_ip_ajax()
	{
	    $id = $this->input->post('id');
	    $ip_address = $this->input->post('ip_address');
	    $description = $this->input->post('description');
	    $is_active = $this->input->post('is_active');

	    $this->load->model('Pengaturan_m');
	    $result = $this->Pengaturan_m->update_api_ip($id, [
	        'ip_address' => $ip_address,
	        'description' => $description,
	        'is_active' => $is_active
	    ]);

	    if ($result) {
	        echo json_encode(['status' => 'success', 'message' => 'IP berhasil diperbarui']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui IP']);
	    }
	}

	private function delete_ip_ajax()
	{
	    $id = $this->input->post('id');

	    $this->load->model('Pengaturan_m');
	    $result = $this->Pengaturan_m->delete_api_ip($id);

	    if ($result) {
	        echo json_encode(['status' => 'success', 'message' => 'IP berhasil dihapus']);
	    } else {
	        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus IP']);
	    }
	}

	private function get_domains_ajax()
	{
	    $this->load->model('Pengaturan_m');
	    $domains = $this->Pengaturan_m->get_api_domains();
	    echo json_encode(['status' => 'success', 'data' => $domains]);
	}

	private function get_ips_ajax()
	{
	    $this->load->model('Pengaturan_m');
	    $ips = $this->Pengaturan_m->get_api_ip_whitelist();
	    echo json_encode(['status' => 'success', 'data' => $ips]);
	}

	private function get_metrics_ajax()
	{
	    $this->load->model('Pengaturan_m');
	    $metrics = $this->Pengaturan_m->get_api_performance_metrics(1); // Last 24 hours
	    $rate_limit_violations = $this->Pengaturan_m->get_rate_limit_violations(24);

	    $data = [
	        'total_requests' => $metrics ? $metrics->total_requests : 0,
	        'success_rate' => $metrics ? $metrics->success_rate : 0,
	        'avg_response_time' => $metrics ? $metrics->avg_response_time : 0,
	        'rate_limit_violations' => $rate_limit_violations
	    ];

	    echo json_encode(['status' => 'success', 'data' => $data]);
	}

	private function get_usage_chart_ajax()
	{
	    $this->load->model('Pengaturan_m');
	    $usage_data = $this->Pengaturan_m->get_api_usage_stats(7);
	    $error_data = $this->Pengaturan_m->get_api_error_stats(7);

	    // Combine usage and error data
	    $combined_data = [];
	    foreach ($usage_data as $usage) {
	        $date = $usage->date;
	        $errors = 0;

	        // Find matching error data for this date
	        foreach ($error_data as $error) {
	            if ($error->date === $date) {
	                $errors = $error->errors;
	                break;
	            }
	        }

	        $combined_data[] = [
	            'date' => date('M d', strtotime($date)),
	            'total_requests' => (int)$usage->total_requests,
	            'errors' => (int)$errors
	        ];
	    }

	    echo json_encode(['status' => 'success', 'data' => $combined_data]);
	}

	private function get_top_endpoints_ajax()
	{
	    $this->load->model('Pengaturan_m');
	    $endpoints = $this->Pengaturan_m->get_top_endpoints(10);
	    echo json_encode(['status' => 'success', 'data' => $endpoints]);
	}
public function bantuan()
{
	ce_hak_akses('admin.pengaturan.bantuan');
	ce_active_menu('admin.pengaturan.bantuan');

	$this->load->library('form_validation');
	$this->form_validation->set_rules('kontak[0][nama]', 'Nama Kontak', 'trim');


	if ($this->form_validation->run() == TRUE) {
		$kontak = $this->input->post('kontak');
	          // Filter out empty entries before saving
	          $filtered_kontak = array_filter($kontak, function($k) {
	              return !empty($k['nama']) || !empty($k['telepon']) || !empty($k['email']) || !empty($k['jabatan']);
	          });

		$post_data['bantuan_kontak'] = json_encode(array_values($filtered_kontak));

		if (ce_set_opsi($post_data)) {
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data kontak bantuan telah tersimpan.';
			ce_set_msg('success', $success);
		} else {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
			ce_set_msg('danger', $danger);
		}

		redirect('pengaturan/bantuan');
	}

	$data['active_tab'] = 'bantuan';
	$data['halaman'] = 'pengaturan_index';
	$data['header'] = 'Pengaturan <small>Call Center / Bantuan</small>';

	$this->load->view('template', $data);
}

public function kontak_darurat()
{
	ce_hak_akses('admin.pengaturan.kontak_darurat');
	ce_active_menu('admin.pengaturan.kontak_darurat');

	$this->load->library('form_validation');
	$this->form_validation->set_rules('kontak_darurat[0][nama_kontak]', 'Nama Kontak', 'trim');

	if ($this->form_validation->run() == TRUE) {
		$kontak_darurat = $this->input->post('kontak_darurat');
		// Filter out empty entries before saving
		$filtered_kontak = array_filter($kontak_darurat, function($k) {
			return !empty($k['nama_kontak']) || !empty($k['no_kontak']) || !empty($k['ikon']);
		});

		$post_data['kontak_darurat'] = json_encode(array_values($filtered_kontak));

		if (ce_set_opsi($post_data)) {
			$success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data kontak darurat telah tersimpan.';
			ce_set_msg('success', $success);
		} else {
			$danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
			ce_set_msg('danger', $danger);
		}

		redirect('pengaturan/kontak_darurat');
	}

	$data['active_tab'] = 'kontak_darurat';
	$data['halaman'] = 'pengaturan_index';
	$data['header'] = 'Pengaturan <small>Kontak Darurat</small>';

	$this->load->view('template', $data);
}
}