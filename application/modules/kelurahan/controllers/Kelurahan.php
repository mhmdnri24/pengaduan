<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Kelurahan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        ce_active_menu('admin.kelurahan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.kelurahan.view');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $data['halaman'] = 'kelurahan';
        $data['javascript'] = array(
            'js/js_datatable' => array('ajax_url' => base_url('kelurahan/ajax_data'))
        );
        $data['header'] = 'Kelurahan <small>Index Data</small>';

        $this->load->view('template', $data);
    }

    public function tambah()
    {
        ce_hak_akses('admin.kelurahan.add');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        if ($this->input->method(true) == 'POST') {
            
            $post_data['id_provinsi'] = abs((int)$this->input->post('id_provinsi'));
            $post_data['id_kota'] = $this->input->post('id_kota');
            $post_data['id_kecamatan'] = $this->input->post('id_kecamatan');
            $post_data['nama_kelurahan'] = $this->input->post('nama_kelurahan');
            $post_data['tipe'] = $this->input->post('tipe');
            $post_data['latlng'] = $this->input->post('latlng');

            if ($this->kelurahan_m->kelurahan_insert_data($post_data)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('kelurahan');
        }

        $data['provinsi'] = $this->provinsi_m->provinsi_get_all();
        $data['halaman'] = 'kelurahan_tambah';

        $data['javascript'] = array(
            'kelurahan_maps' => null
        );

        $data['header'] = 'Kelurahan <small>Tambah Data</small>';

        $this->load->view('template', $data);
    }

    public function edit($id)
    {
        ce_hak_akses('admin.kelurahan.update');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        if ($this->input->method(true) == 'POST') {
            
            $post_data['id_provinsi'] = abs((int)$this->input->post('id_provinsi'));
            $post_data['id_kota'] = $this->input->post('id_kota');
            $post_data['id_kecamatan'] = $this->input->post('id_kecamatan');
            $post_data['nama_kelurahan'] = $this->input->post('nama_kelurahan');
            $post_data['tipe'] = $this->input->post('tipe');
            $post_data['latlng'] = $this->input->post('latlng');

            if ($this->kelurahan_m->kelurahan_update_data($post_data, $id)) {
                $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda masukan telah tersimpan.';
                ce_set_msg('success', $success);
            } else {
                $danger = '<h4><i class="icon fa fa-ban"></i>Ups!</h4> Data yang Anda masukan gagal tersimpan.';
                ce_set_msg('danger', $danger);
            }

            redirect('kelurahan');
        }

        $data['provinsi'] = $this->provinsi_m->provinsi_get_all();
        $data['kota'] = $this->kota_m->kota_get_all();
        $data['kecamatan'] = $this->kecamatan_m->kecamatan_get_all();
        $data['kelurahan'] = $this->kelurahan_m->kelurahan_by_id($id);
        $data['halaman'] = 'kelurahan_edit';
        /*
        $data['javascript'] = array(
            'kelurahan_js' => ['detailkel'=>$data['kelurahan']]
        );
        */
        $data['header'] = 'Kelurahan <small>Edit Data</small>';
        $data['javascript'] = array(
            'kelurahan_maps_edit' => null
        );
        $this->load->view('template', $data);
    }

    public function hapus($id)
    {
        ce_hak_akses('admin.kelurahan.delete');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $this->kelurahan_m->kelurahan_delete_data($id);
        $success = '<h4><i class="icon fa fa-check"></i>Berhasil!</h4> Data yang Anda pilih telah dihapus.';
        ce_set_msg('success', $success);

        redirect('kelurahan');
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.kelurahan.view');
        if (!$this->session->userdata('user_login')) {
            redirect('user/login');
            exit;
        }

        $dataConfig = array(
            'table' => 'kelurahan',
            'select' => 'kelurahan.id_kelurahan, kelurahan.nama_kelurahan, kecamatan.nama_kecamatan, kota.nama_kota, provinsi.nama_provinsi',
            'column_order' => array(null, 'kelurahan.nama_kelurahan', 'kelurahan.id_kelurahan', 'kecamatan.nama_kecamatan', 'kota.nama_kota', 'provinsi.nama_provinsi', null),
            'column_search' => array('kelurahan.nama_kelurahan', 'kecamatan.nama_kecamatan', 'kota.nama_kota', 'provinsi.nama_provinsi'),
            'join' => array(
                array('provinsi', 'kelurahan.id_provinsi=provinsi.id_provinsi', 'left'),
                array('kota', 'kelurahan.id_kota=kota.id_kota', 'left'),
                array('kecamatan', 'kelurahan.id_kecamatan=kecamatan.id_kecamatan', 'left')
            ),
            'order' => array('id_kelurahan' => 'asc')
        );
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();

        $data = array();
        $no = $this->input->post('start');
        foreach ($list as $field) {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = $field->nama_kelurahan;
            $row[] = $field->id_kelurahan;
            $row[] = $field->nama_kecamatan;
            $row[] = $field->nama_kota;
            $row[] = $field->nama_provinsi;
            $button = '<div class="btn-group pull-right">
					<button type="button" class="btn btn-default dropdown-toggle btn-sm" data-toggle="dropdown">
					Aksi <span class="caret"></span>
					</button>
					<ul class="dropdown-menu pull-right" role="menu">
						<li>' . ce_anchor('admin.kelurahan.update', 'kelurahan/edit/' . $field->id_kelurahan, '<i class="fa fa-edit"></i>Edit Data') . '</li>
						<!--<li>' . ce_anchor('admin.kelurahan.delete', 'kelurahan/hapus/' . $field->id_kelurahan, '<i class="fa fa-trash"></i>Hapus Data', 'onclick="return delete_confirm();"') . '</li>-->
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
               
        echo json_encode($output);
    }

    public function get_option($id_kecamatan)
    {
        $output = '<option value="0">Semua Kelurahan</option>';
        $kelurahan = $this->kelurahan_m->kelurahan_by_kecamatan($id_kecamatan);
        foreach ($kelurahan as $row) {
            $output .= '<option value="' . $row->id_kelurahan . '">' . $row->nama_kelurahan . '</option>';
        }
        echo $output;
    }

    public function getgejson($id)
    {

        $data = $this->db->get_where('kelurahan', array('id_kelurahan' => $id))->row_array();
        if(null === $data) {

            $res = null;
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
