<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Provinsi extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.provinsi.view');
    }

    public function index()
    {
        ce_hak_akses('admin.provinsi.view');

        $data['halaman'] = 'provinsi';
        $data['javascript'] = array(
            'js/js_datatable' => array('ajax_url' => base_url('provinsi/ajax_data'))
        );
        $data['header'] = 'Provinsi <small>Index Data</small>';

        $this->load->view('template', $data);
    }

    public function tambah()
    {
        ce_hak_akses('admin.provinsi.add');

        if ($this->input->method(true) == 'POST') {
            $post_data['id_provinsi'] = $this->input->post('id_provinsi');
            $post_data['nama_provinsi'] = $this->input->post('nama_provinsi');

            if ($this->provinsi_m->provinsi_insert_data($post_data)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('provinsi');
        }

        $data['halaman'] = 'provinsi_tambah';
        $data['header'] = 'Provinsi <small>Tambah Data</small>';

        $this->load->view('template', $data);
    }

    public function edit($id)
    {
        ce_hak_akses('admin.provinsi.update');

        if ($this->input->method(true) == 'POST') {
            $post_data['id_provinsi'] = $this->input->post('id_provinsi');
            $post_data['nama_provinsi'] = $this->input->post('nama_provinsi');

            if ($this->provinsi_m->provinsi_update_data($post_data, $id)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('provinsi');
        }

        $data['provinsi'] = $this->provinsi_m->provinsi_by_id($id);
        $data['halaman'] = 'provinsi_edit';
        $data['header'] = 'Provinsi <small>Edit Data</small>';

        $this->load->view('template', $data);
    }

    public function hapus($id)
    {
        ce_hak_akses('admin.provinsi.delete');

        $this->provinsi_m->provinsi_delete_data($id);
        $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
        ce_set_msg('success', $success);

        redirect('provinsi');
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.provinsi.view');

        $dataConfig = array(
            'table' => 'provinsi',
            'select' => 'id_provinsi, nama_provinsi',
            'column_order' => array(null, 'nama_provinsi', null),
            'column_search' => array('nama_provinsi'),
            'order' => array('id_provinsi' => 'asc')
        );
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();

        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            // $row[] = $field->id_provinsi;
            $row[] = $field->nama_provinsi;
            $button = '<div class="btn-group pull-right">
					<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown">
					Aksi <span class="caret"></span>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>' . ce_anchor('admin.provinsi.update', 'provinsi/edit/' . $field->id_provinsi, '<i class="fa fa-edit"></i>Edit Data') . '</li>
						<!--<li>' . ce_anchor('admin.provinsi.delete', 'provinsi/hapus/' . $field->id_provinsi, '<i class="fa fa-trash"></i>Hapus Data', 'onclick="return delete_confirm();"') . '</li>-->
					</ul>
				</div>';
            $row[] = $button;

            $data[] = $row;
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data
        );

        //output dalam format JSON
        
        echo json_encode($output);
    }
}