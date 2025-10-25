<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kecamatan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        ce_active_menu('admin.kecamatan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kecamatan.view');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $data['halaman'] = 'kecamatan';
        $data['javascript'] = array(
            'js/js_datatable' => array('ajax_url' => base_url('kecamatan/ajax_data'))
        );
        $data['header'] = 'Kecamatan <small>Index Data</small>';

        $this->load->view('template', $data);
    }

    public function tambah()
    {
        ce_hak_akses('admin.kecamatan.add');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        if ($this->input->method(true) == 'POST') {
            
            $post_data['id_provinsi'] = abs((int)$this->input->post('id_provinsi'));
            $post_data['id_kota'] = $this->input->post('id_kota');
            $post_data['nama_kecamatan'] = $this->input->post('nama_kecamatan');

            if ($this->kecamatan_m->kecamatan_insert_data($post_data)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('kecamatan');
        }

        $data['provinsi'] = $this->provinsi_m->provinsi_get_all();
        $data['halaman'] = 'kecamatan_tambah';
        $data['header'] = 'Kecamatan <small>Tambah Data</small>';

        $this->load->view('template', $data);
    }

    public function edit($id)
    {
        ce_hak_akses('admin.kecamatan.update');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        if ($this->input->method(true) == 'POST') {
            
            $post_data['id_provinsi'] = abs((int)$this->input->post('id_provinsi'));
            $post_data['id_kota'] = $this->input->post('id_kota');
            $post_data['nama_kecamatan'] = $this->input->post('nama_kecamatan');
            $post_data['tipe'] = $this->input->post('tipe');
            $post_data['latlng'] = $this->input->post('latlng');

            if ($this->kecamatan_m->kecamatan_update_data($post_data, $id)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('kecamatan');
        }

        $data['javascript'] = array(
            'kecamatan_maps_edit' => null
        );

        $data['provinsi'] = $this->provinsi_m->provinsi_get_all();
        $data['kota'] = $this->kota_m->kota_get_all();
        $data['kecamatan'] = $this->kecamatan_m->kecamatan_by_id($id);
        $data['halaman'] = 'kecamatan_edit';
        $data['header'] = 'Kecamatan <small>Edit Data</small>';

        $this->load->view('template', $data);
    }

    public function hapus($id)
    {
        ce_hak_akses('admin.kecamatan.delete');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $this->kecamatan_m->kecamatan_delete_data($id);
        $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
        ce_set_msg('success', $success);

        redirect('kecamatan');
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kecamatan.view');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $dataConfig = array(
            'table' => 'kecamatan',
            'select' => 'kecamatan.id_kecamatan, kecamatan.nama_kecamatan, kota.nama_kota, provinsi.nama_provinsi',
            'column_order' => array(null, 'kecamatan.nama_kecamatan', 'kota.nama_kota', 'provinsi.nama_provinsi', null),
            'column_search' => array('kecamatan.nama_kecamatan', 'kota.nama_kota', 'provinsi.nama_provinsi'),
            'join' => array(
                array('provinsi', 'kecamatan.id_provinsi=provinsi.id_provinsi', 'left'),
                array('kota', 'kecamatan.id_kota=kota.id_kota', 'left')
            ),
            'order' => array('id_kecamatan' => 'asc')
        );
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();

        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = strtoupper($field->nama_kecamatan);
            $row[] = strtoupper($field->nama_kota);
            $row[] = strtoupper($field->nama_provinsi);
            // $row[] = $field->id_kecamatan;
            $button = '<div class="btn-group pull-right">
					<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown">
					Aksi <span class="caret"></span>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>' . ce_anchor('admin.kecamatan.update', 'kecamatan/edit/' . $field->id_kecamatan, '<i class="fa fa-edit"></i>Edit Data') . '</li>
						<!--<li>' . ce_anchor('admin.kecamatan.delete', 'kecamatan/hapus/' . $field->id_kecamatan, '<i class="fa fa-trash"></i>Hapus Data', 'onclick="return delete_confirm();"') . '</li>-->
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

    public function get_option($id_kota)
    {
        $output = '<option value="0">Semua Kecamatan</option>';
        $kecamatan = $this->kecamatan_m->kecamatan_by_provinsi($id_kota);
        foreach ($kecamatan as $row) {
            $output .= '<option value="' . $row->id_kecamatan . '">' . $row->nama_kecamatan . '</option>';
        }
        echo $output;
    }

    public function getgejson($id)
    {

        $data = $this->db->get_where('kecamatan', array('id_kecamatan' => $id))->row_array();
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
                  "type" => "MultiPolygon",
                  "coordinates" => [
                    [
                        json_decode($res['latlng'], true)


                    ]
                  ]
                ]
              ]

          ]

            ];

        echo json_encode($arr);
    }
}
