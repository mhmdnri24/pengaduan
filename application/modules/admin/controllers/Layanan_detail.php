<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Layanan_detail extends Admin_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
        $this->load->helper('url');
        $this->load->helper('form');
        
        // Check permission
        if (!$this->ion_auth->logged_in()) {
            redirect('auth/login');
        }
    }

    public function index()
    {
        // Check permission
        if (!$this->check_permission('admin.layanan_detail.view')) {
            show_error('Anda tidak memiliki hak akses untuk melihat halaman ini.', 403, 'Akses Ditolak');
        }

        $data['title'] = 'Detail Layanan';
        $data['breadcrumb'] = [
            ['title' => 'Dashboard', 'url' => base_url('admin/dashboard')],
            ['title' => 'Detail Layanan', 'url' => '']
        ];

        // Get data with pagination
        $this->load->library('pagination');
        
        $config['base_url'] = base_url('admin/layanan_detail/index');
        $config['total_rows'] = $this->Layanan_detail_model->count_all();
        $config['per_page'] = 10;
        $config['uri_segment'] = 4;
        
        // Pagination styling
        $config['full_tag_open'] = '<nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['first_link'] = 'First';
        $config['last_link'] = 'Last';
        $config['first_tag_open'] = '<li class="page-item">';
        $config['first_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['next_link'] = '&raquo';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li class="page-item">';
        $config['last_tag_close'] = '</li>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);
        
        $page = ($this->uri->segment(4)) ? $this->uri->segment(4) : 0;
        $data['layanan_detail'] = $this->Layanan_detail_model->get_all($config['per_page'], $page);
        $data['pagination'] = $this->pagination->create_links();

        $this->load->view('admin/layanan_detail/index', $data);
    }

    public function add()
    {
        // Check permission
        if (!$this->check_permission('admin.layanan_detail.add')) {
            show_error('Anda tidak memiliki hak akses untuk menambah data.', 403, 'Akses Ditolak');
        }

        $data['title'] = 'Tambah Detail Layanan';
        $data['breadcrumb'] = [
            ['title' => 'Dashboard', 'url' => base_url('admin/dashboard')],
            ['title' => 'Detail Layanan', 'url' => base_url('admin/layanan_detail')],
            ['title' => 'Tambah Detail Layanan', 'url' => '']
        ];

        // Get jenis layanan for dropdown
        $data['jenis_layanan'] = $this->Jenis_layanan_model->get_all_active();

        if ($this->input->post()) {
            $this->form_validation->set_rules('jenis_layanan_id', 'Jenis Layanan', 'required|numeric');
            $this->form_validation->set_rules('nama_layanan', 'Nama Layanan', 'required|trim|max_length[255]');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim');
            $this->form_validation->set_rules('harga', 'Harga', 'required|numeric');
            $this->form_validation->set_rules('durasi', 'Durasi', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[aktif,nonaktif]');

            if ($this->form_validation->run() == TRUE) {
                $insert_data = [
                    'jenis_layanan_id' => $this->input->post('jenis_layanan_id'),
                    'nama_layanan' => $this->input->post('nama_layanan'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'harga' => $this->input->post('harga'),
                    'durasi' => $this->input->post('durasi'),
                    'status' => $this->input->post('status'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->Layanan_detail_model->insert($insert_data)) {
                    $this->session->set_flashdata('success', 'Detail layanan berhasil ditambahkan.');
                    redirect('admin/layanan_detail');
                } else {
                    $this->session->set_flashdata('error', 'Gagal menambahkan detail layanan.');
                }
            }
        }

        $this->load->view('admin/layanan_detail/add', $data);
    }

    public function edit($id = null)
    {
        // Check permission
        if (!$this->check_permission('admin.layanan_detail.update')) {
            show_error('Anda tidak memiliki hak akses untuk mengedit data.', 403, 'Akses Ditolak');
        }

        if (!$id) {
            show_404();
        }

        $layanan_detail = $this->Layanan_detail_model->get_by_id($id);
        if (!$layanan_detail) {
            show_404();
        }

        $data['title'] = 'Edit Detail Layanan';
        $data['breadcrumb'] = [
            ['title' => 'Dashboard', 'url' => base_url('admin/dashboard')],
            ['title' => 'Detail Layanan', 'url' => base_url('admin/layanan_detail')],
            ['title' => 'Edit Detail Layanan', 'url' => '']
        ];

        $data['layanan_detail'] = $layanan_detail;
        $data['jenis_layanan'] = $this->Jenis_layanan_model->get_all_active();

        if ($this->input->post()) {
            $this->form_validation->set_rules('jenis_layanan_id', 'Jenis Layanan', 'required|numeric');
            $this->form_validation->set_rules('nama_layanan', 'Nama Layanan', 'required|trim|max_length[255]');
            $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim');
            $this->form_validation->set_rules('harga', 'Harga', 'required|numeric');
            $this->form_validation->set_rules('durasi', 'Durasi', 'required|trim|max_length[100]');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[aktif,nonaktif]');

            if ($this->form_validation->run() == TRUE) {
                $update_data = [
                    'jenis_layanan_id' => $this->input->post('jenis_layanan_id'),
                    'nama_layanan' => $this->input->post('nama_layanan'),
                    'deskripsi' => $this->input->post('deskripsi'),
                    'harga' => $this->input->post('harga'),
                    'durasi' => $this->input->post('durasi'),
                    'status' => $this->input->post('status'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($this->Layanan_detail_model->update($id, $update_data)) {
                    $this->session->set_flashdata('success', 'Detail layanan berhasil diperbarui.');
                    redirect('admin/layanan_detail');
                } else {
                    $this->session->set_flashdata('error', 'Gagal memperbarui detail layanan.');
                }
            }
        }

        $this->load->view('admin/layanan_detail/edit', $data);
    }

    public function delete($id = null)
    {
        // Check permission
        if (!$this->check_permission('admin.layanan_detail.delete')) {
            show_error('Anda tidak memiliki hak akses untuk menghapus data.', 403, 'Akses Ditolak');
        }

        if (!$id) {
            show_404();
        }

        $layanan_detail = $this->Layanan_detail_model->get_by_id($id);
        if (!$layanan_detail) {
            show_404();
        }

        if ($this->Layanan_detail_model->delete($id)) {
            $this->session->set_flashdata('success', 'Detail layanan berhasil dihapus.');
        } else {
            $this->session->set_flashdata('error', 'Gagal menghapus detail layanan.');
        }

        redirect('admin/layanan_detail');
    }

    public function view($id = null)
    {
        // Check permission
        if (!$this->check_permission('admin.layanan_detail.view')) {
            show_error('Anda tidak memiliki hak akses untuk melihat data.', 403, 'Akses Ditolak');
        }

        if (!$id) {
            show_404();
        }

        $layanan_detail = $this->Layanan_detail_model->get_by_id_with_jenis($id);
        if (!$layanan_detail) {
            show_404();
        }

        $data['title'] = 'Detail Layanan';
        $data['breadcrumb'] = [
            ['title' => 'Dashboard', 'url' => base_url('admin/dashboard')],
            ['title' => 'Detail Layanan', 'url' => base_url('admin/layanan_detail')],
            ['title' => 'Lihat Detail', 'url' => '']
        ];

        $data['layanan_detail'] = $layanan_detail;

        $this->load->view('admin/layanan_detail/view', $data);
    }
}
