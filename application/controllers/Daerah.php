<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Daerah extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('daerah_m');
        $this->load->library('security');
    }

    public function option_kota($prov_id)
    {
        $result = $this->daerah_m->get_kota_lists($prov_id);
        echo '<option value=""></option>';
        foreach ($result as $row) {
            echo '<option value="' . $row->kota_id . '">' . $row->kota_nama . '</option>';
        }
    }

    public function option_kecamatan($kota_id)
    {
        $result = $this->daerah_m->get_kecamatan_lists($kota_id);
        echo '<option value=""></option>';
        foreach ($result as $row) {
            echo '<option value="' . $row->id_kecamatan . '">' . $row->nama_kecamatan . '</option>';
        }
    }

    public function option_kelurahan($kec_id)
    {
        $result = $this->daerah_m->get_kelurahan_lists($kec_id);
        echo '<option value=""></option>';
        foreach ($result as $row) {
            echo '<option value="' . $row->id_kelurahan . '">' . $row->nama_kelurahan . '</option>';
        }
    }

    // JSON endpoint untuk kecamatan
    public function get_kecamatan()
    {
        $id_kota = $this->input->post('id_kota');
        
        if (empty($id_kota)) {
            $response = [
                'status' => false,
                'message' => 'ID Kota tidak valid',
                'data' => [],
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        } else {
            $result = $this->daerah_m->get_kecamatan_by_kota($id_kota);
            $response = [
                'status' => true,
                'data' => $result,
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }

    // JSON endpoint untuk kelurahan
    public function get_kelurahan()
    {
        $id_kecamatan = $this->input->post('id_kecamatan');
        
        if (empty($id_kecamatan)) {
            $response = [
                'status' => false,
                'message' => 'ID Kecamatan tidak valid',
                'data' => [],
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        } else {
            $result = $this->daerah_m->get_kelurahan_by_kecamatan($id_kecamatan);
            $response = [
                'status' => true,
                'data' => $result,
                'csrf_hash' => $this->security->get_csrf_hash()
            ];
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}