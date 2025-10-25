<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kota extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        ce_active_menu('admin.kota.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kota.view');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $data['halaman'] = 'kota';
        $data['javascript'] = array(
            'js/js_datatable' => array('ajax_url' => base_url('kota/ajax_data'))
        );
        $data['header'] = 'Kota <small>Index Data</small>';

        $this->load->view('template', $data);
    }

    public function tambah()
    {
        ce_hak_akses('admin.kota.add');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        if ($this->input->method(true) == 'POST') {
            $post_data['id_provinsi'] = abs((int)$this->input->post('id_provinsi'));
            $post_data['id_kota'] = $this->input->post('id_kota');
            $post_data['nama_kota'] = $this->input->post('nama_kota');

            if ($this->kota_m->kota_insert_data($post_data)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('kota');
        }

        $data['provinsi'] = $this->provinsi_m->provinsi_get_all();
        $data['halaman'] = 'kota_tambah';
        $data['header'] = 'Kota <small>Tambah Data</small>';

        $this->load->view('template', $data);
    }

    public function edit($id)
    {
        ce_hak_akses('admin.kota.update');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        if ($this->input->method(true) == 'POST') {
            $post_data['id_provinsi'] = abs((int)$this->input->post('id_provinsi'));
            $post_data['id_kota'] = $this->input->post('id_kota');
            $post_data['nama_kota'] = $this->input->post('nama_kota');
            $post_data['tipe'] = $this->input->post('tipe');
            $post_data['latlng'] = $this->input->post('latlng');

            if ($this->kota_m->kota_update_data($post_data, $id)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('kota');
        }

        $data['javascript'] = array(
            'kota_maps_edit' => null
        );

        $data['provinsi'] = $this->provinsi_m->provinsi_get_all();
        $data['kota'] = $this->kota_m->kota_by_id($id);
        $data['halaman'] = 'kota_edit';
        $data['header'] = 'Kota <small>Edit Data</small>';

        $this->load->view('template', $data);
    }

    public function hapus($id)
    {
        ce_hak_akses('admin.kota.delete');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $this->kota_m->kota_delete_data($id);
        $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
        ce_set_msg('success', $success);

        redirect('kota');
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kota.view');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $dataConfig = array(
            'table' => 'kota',
            'select' => 'kota.id_kota, kota.nama_kota, provinsi.nama_provinsi',
            'column_order' => array(null, 'kota.nama_kota', 'provinsi.nama_provinsi', null),
            'column_search' => array('kota.nama_kota', 'provinsi.nama_provinsi'),
            'join' => array(
                array('provinsi', 'kota.id_provinsi=provinsi.id_provinsi', 'left')
            ),
            'order' => array('id_kota' => 'asc')
        );
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();

        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            // $row[] = $field->id_kota;
            $row[] = $field->nama_kota;
            $row[] = $field->nama_provinsi;
            $button = '<div class="btn-group pull-right">
					<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown">
					Aksi <span class="caret"></span>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>' . ce_anchor('admin.kota.update', 'kota/edit/' . $field->id_kota, '<i class="fa fa-edit"></i>Edit Data') . '</li>
						<!--<li>' . ce_anchor('admin.kota.delete', 'kota/hapus/' . $field->id_kota, '<i class="fa fa-trash"></i>Hapus Data', 'onclick="return delete_confirm();"') . '</li>-->
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

    public function get_option($id_provinsi)
    {
        $output = '<option value="0">Semua Kota</option>';
        $kota = $this->kota_m->kota_by_provinsi($id_provinsi);
        foreach ($kota as $row) {
            $output .= '<option value="' . $row->id_kota . '">' . $row->nama_kota . '</option>';
        }
        echo $output;
    }

    public function getgejson($id)
    {

        $data = $this->db->get_where('kota', array('id_kota' => $id))->row_array();
        if(null === $data) {

            $res = null;
            //   echo json_encode($res);
            //   exit();
        }

        $res = $data;

        $arr = [
          "type" => "FeatureCollection",
          "name" => "",
        "crs" => [
          "type" => "name",
          "properties" => [
            "name" => "urn:ogc:def:crs:OGC:1.3:CRS84"
          ]
        ],
        "features" => [
               [
                "type" => "Feature",
                "properties" => [
                  "country_code" => "id",
                ],
                "geometry" => [
                  "type" => $res['tipe'] == 'marker' ? 'Point' : 'MultiPolygon' ,
                  "coordinates" => $res['tipe'] == 'marker' ? json_decode($res['latlng'], true) : [[ json_decode($res['latlng'], true) ]]
                ]
              ]

          ]

        ];

        echo json_encode($arr);
    }
}