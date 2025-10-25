<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Whatsapp extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        
        $this->load->model('whatsapp/wa_template_m');
        $this->load->model('whatsapp/wa_histori_m');
    }

    public function index()
    {
        redirect('whatsapp/template');
    }

    public function template()
    {
        ce_hak_akses('admin.whatsapp.template');
        ce_active_menu('admin.whatsapp.template');
        
        $data['halaman'] = 'whatsapp_template';
        $data['header'] = 'WhatsApp <small>Template</small>';
        $data['templates'] = $this->wa_template_m->get_all_templates();

        $this->load->view('template', $data);
    }

    public function tambah_template()
    {
        ce_hak_akses('admin.whatsapp.template');
        ce_active_menu('admin.whatsapp.template');
        
        $data['halaman'] = 'whatsapp_template_form';
        $data['header'] = 'WhatsApp <small>Tambah Template</small>';
        $data['action'] = 'tambah';

        if ($this->input->method(TRUE) == 'POST') {
            $this->form_validation->set_rules('nama', 'Nama Template', 'required|trim');
            $this->form_validation->set_rules('kode', 'Kode Template', 'required|trim|alpha_dash|is_unique[wa_template.kode]');
            $this->form_validation->set_rules('isi_template', 'Isi Template', 'required|trim');
            
            if ($this->form_validation->run() == TRUE) {
                $data_insert = [
                    'nama' => $this->input->post('nama'),
                    'kode' => $this->input->post('kode'),
                    'isi_template' => $this->input->post('isi_template'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'created_by' => $this->session->userdata('user_id')
                ];

                if ($this->wa_template_m->save_with_emoji($data_insert)) {
                    $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Template berhasil ditambahkan.';
                    ce_set_msg('success', $success);
                    redirect('whatsapp/template');
                } else {
                    $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Template gagal ditambahkan.';
                    ce_set_msg('danger', $danger);
                }
            }
        }

        $this->load->view('template', $data);
    }

    public function edit_template($id)
    {
        ce_hak_akses('admin.whatsapp.template');
        ce_active_menu('admin.whatsapp.template');
        
        $data['halaman'] = 'whatsapp_template_form';
        $data['header'] = 'WhatsApp <small>Edit Template</small>';
        $data['action'] = 'edit';
        $data['template'] = $this->wa_template_m->get_by_id($id);

        if (!$data['template']) {
            $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Template tidak ditemukan.';
            ce_set_msg('danger', $danger);
            redirect('whatsapp/template');
        }

        if ($this->input->method(TRUE) == 'POST') {
            $this->form_validation->set_rules('nama', 'Nama Template', 'required|trim');
            
            // Check apakah kode diubah
            if ($this->input->post('kode') != $data['template']->kode) {
                $this->form_validation->set_rules('kode', 'Kode Template', 'required|trim|alpha_dash|is_unique[wa_template.kode]');
            } else {
                $this->form_validation->set_rules('kode', 'Kode Template', 'required|trim|alpha_dash');
            }
            
            $this->form_validation->set_rules('isi_template', 'Isi Template', 'required|trim');
            
            if ($this->form_validation->run() == TRUE) {
                $data_update = [
                    'nama' => $this->input->post('nama'),
                    'kode' => $this->input->post('kode'),
                    'isi_template' => $this->input->post('isi_template'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'updated_by' => $this->session->userdata('user_id')
                ];

                if ($this->wa_template_m->update_with_emoji($id, $data_update)) {
                    $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Template berhasil diperbarui.';
                    ce_set_msg('success', $success);
                    redirect('whatsapp/template');
                } else {
                    $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Template gagal diperbarui.';
                    ce_set_msg('danger', $danger);
                }
            }
        }

        $this->load->view('template', $data);
    }

    public function hapus_template($id)
    {
        ce_hak_akses('admin.whatsapp.template');
        
        $template = $this->wa_template_m->get_by_id($id);

        if (!$template) {
            $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Template tidak ditemukan.';
            ce_set_msg('danger', $danger);
            redirect('whatsapp/template');
        }

        if ($this->wa_template_m->delete($id)) {
            $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Template berhasil dihapus.';
            ce_set_msg('success', $success);
        } else {
            $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Template gagal dihapus.';
            ce_set_msg('danger', $danger);
        }
        
        redirect('whatsapp/template');
    }
    
    public function preview_template()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }

        $template = $this->input->post('isi_template');
        $params = $this->input->post('params');
        $params_array = [];

        if ($params) {
            $params_lines = explode("\n", $params);
            foreach ($params_lines as $line) {
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    $params_array[$key] = $value;
                }
            }
        }

        // Ganti semua placeholder dengan nilai parameter
        $pesan = $template;
        foreach ($params_array as $key => $value) {
            $pesan = str_replace('{'.$key.'}', $value, $pesan);
        }

        // Set header content type to JSON
        
        echo json_encode(['status' => true, 'preview' => $pesan]);
        exit;
    }

    public function histori()
    {
        ce_hak_akses('admin.whatsapp.histori');
        ce_active_menu('admin.whatsapp.histori');
        
        $data['halaman'] = 'whatsapp_histori';
        $data['header'] = 'WhatsApp <small>Histori</small>';
        
        // Filter data
        $filter = [
            'keyword' => $this->input->get('keyword'),
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'status' => $this->input->get('status'),
        ];
        
        // Pagination
        $this->load->library('pagination');
        
        $config['base_url'] = site_url('whatsapp/histori');
        $config['total_rows'] = $this->wa_histori_m->count_all($filter);
        $config['per_page'] = 20;
        $config['page_query_string'] = TRUE;
        $config['query_string_segment'] = 'page';
        
        $this->pagination->initialize($config);
        
        $page = $this->input->get('page') ? $this->input->get('page') : 0;
        
        $data['histori'] = $this->wa_histori_m->get_all($config['per_page'], $page, $filter);
        $data['filter'] = $filter;
        
        $this->load->view('template', $data);
    }
    
    public function detail_histori($id)
    {
        ce_hak_akses('admin.whatsapp.histori');
        ce_active_menu('admin.whatsapp.histori');
        
        $data['halaman'] = 'whatsapp_histori_detail';
        $data['header'] = 'WhatsApp <small>Detail Histori</small>';
        $data['histori'] = $this->wa_histori_m->get_by_id($id);
        
        if (!$data['histori']) {
            $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Histori tidak ditemukan.';
            ce_set_msg('danger', $danger);
            redirect('whatsapp/histori');
        }
        
        // Jika ada template_id, ambil data template
        if ($data['histori']->template_id) {
            $data['template'] = $this->wa_template_m->get_by_id($data['histori']->template_id);
        }
        
        $this->load->view('template', $data);
    }

    /**
     * Halaman kirim pesan WhatsApp
     */
    public function kirim()
    {
        ce_hak_akses('admin.whatsapp.kirim');
        ce_active_menu('admin.whatsapp.kirim');
        
        $data['halaman'] = 'whatsapp_kirim';
        $data['header'] = 'WhatsApp <small>Kirim Pesan</small>';
        
        // Load daftar template
        $data['templates'] = $this->wa_template_m->get_all_templates();
        
                
        if ($this->input->method(TRUE) == 'POST') {
            $this->form_validation->set_rules('nomor', 'Nomor HP', 'required|trim');
            $this->form_validation->set_rules('pesan', 'Pesan', 'required|trim');
            
            if ($this->form_validation->run() == TRUE) {
                $nomor = $this->input->post('nomor');
                $pesan = $this->input->post('pesan');
                $template_id = $this->input->post('template_id');
                $nama_tujuan = $this->input->post('nama_tujuan');
                
                $options = [
                    'nama_tujuan' => $nama_tujuan,
                    'created_by' => $this->session->userdata('user_id')
                ];
                
                if ($template_id) {
                    $options['template_id'] = $template_id;
                }
                
                $response = kirim_pesan($nomor, $pesan, $options);
                
                if (isset($response['status']) && $response['status']) {
                    $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Pesan berhasil dikirim.';
                    ce_set_msg('success', $success);
                } else {
                    $error_msg = isset($response['message']) ? $response['message'] : 'Gagal mengirim pesan';
                    $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> ' . $error_msg;
                    ce_set_msg('danger', $danger);
                }
                
                redirect('whatsapp/kirim');
            }
        }
        
        $this->load->view('template', $data);
    }
    
    /**
     * Mendapatkan isi template - untuk AJAX
     */
    public function get_template()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        
        $template_id = $this->input->post('template_id');
        $template = $this->wa_template_m->get_by_id($template_id);
        
        if ($template) {
            echo json_encode(['success' => true, 'data' => $template]);
        } else {
            echo json_encode(['success' => false]);
        }
    }
} 