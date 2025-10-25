<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pelaporan extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->session->user_login) {
            redirect('user/login');
            exit;
        }
        ce_active_menu('admin.pelaporan.view');
    }

    public function index()
    {
        ce_hak_akses('admin.pelaporan.view');

        // Load additional models
        $this->load->model('user_m');

        $data['header'] = 'Pelaporan <small>Daftar Laporan</small>';
        $data['halaman'] = 'pelaporan';
        $data['javascript'] = array(
            'pelaporan/js_pelaporan' => null
        );

        // Data untuk filter unit kerja
        $data['unitkerja_options'] = $this->user_m->get_unitkerja_options();

        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        ce_hak_akses('admin.pelaporan.view');

        $dataConfig = [
            'table' => 'pelaporan p',
            'select' => 'p.id, p.kode_laporan, p.judul, p.pelapor_nama, p.status, p.prioritas, p.created_at, kp.pelaporan_nama as nama_kategori, uk.unitkerja as nama_unitkerja',
            'column_order' => [null, 'p.kode_laporan', 'p.judul', 'kp.pelaporan_nama', 'p.status', 'p.created_at', null],
            'column_search' => ['p.kode_laporan', 'p.judul', 'p.pelapor_nama', 'p.alamat', 'kp.pelaporan_nama', 'uk.unitkerja'],
            'join' => [
                ['kategori_pelaporan kp', 'p.kategori = kp.pelaporan_nama', 'left'],
                ['user u', 'p.created_by = u.id_user', 'left'],
                ['tbUnitKerja uk', 'p.unitkerja_id = uk.id_unitkerja', 'left']
            ],
            'order' => ['p.created_at' => 'desc']
        ];

        // Filter berdasarkan unit kerja user jika bukan admin (id_level != 1)
        $user_id_unitkerja = $this->session->userdata('id_unitkerja');
        $user_level = $this->session->userdata('id_level');

        if ($user_level != 1 && !empty($user_id_unitkerja)) {
            // Filter data berdasarkan unit kerja laporan
            $dataConfig['condition']['p.unitkerja_id'] = $user_id_unitkerja;
        }

        // Filter berdasarkan status
        $status = $this->input->post('status');
        $kategori = $this->input->post('kategori');
        $prioritas = $this->input->post('prioritas');
        $unitkerja = $this->input->post('unitkerja');

        if ($status !== '' && $status !== null) {
            $dataConfig['condition']['p.status'] = $status;
        }

        if ($kategori !== '' && $kategori !== null) {
            $dataConfig['condition']['kp.pelaporan_nama'] = $kategori;
        }

        if ($prioritas !== '' && $prioritas !== null) {
            $dataConfig['condition']['p.prioritas'] = $prioritas;
        }

        if ($unitkerja !== '' && $unitkerja !== null && $user_level == 1) {
            $dataConfig['condition']['p.unitkerja_id'] = $unitkerja;
        }
        
        $this->ajax_data_m->data_config($dataConfig);
        $list = $this->ajax_data_m->get_datatables();
        // Check permissions outside the loop for better performance
        $user_level = $this->session->userdata('id_level');

        // For super admin, always allow all actions
        if ($user_level == 1) {
            $can_detail = true;
            $can_update = true;
            $can_update_status = true;
            $can_delete = true;
        } else {
            // For other users, check permissions from database
            $this->load->model('hak_akses/level_m');
            $level_data = $this->level_m->level_by_id($user_level);
            $hak_akses = [];

            if ($level_data && !empty($level_data->hak_akses) && $level_data->hak_akses !== 'null') {
                $hak_akses = json_decode($level_data->hak_akses, true);
                $hak_akses = is_array($hak_akses) ? $hak_akses : [];
            }

            $can_detail = in_array('admin.pelaporan.detail', $hak_akses);
            $can_update = in_array('admin.pelaporan.update', $hak_akses);
            $can_update_status = in_array('admin.pelaporan.update_status', $hak_akses);
            $can_delete = in_array('admin.pelaporan.delete', $hak_akses);
        }

        $data = array();
        $no = $this->input->post('start');

        foreach ($list as $item) {
            $no++;
            $row = array();
            $row[] = $no;
            // Kode Laporan dengan nama pelapor di bawahnya
            $kode_dengan_pelapor = '<div class="kode-laporan-cell"><strong>' . $item->kode_laporan . '</strong><br><small>' . $item->pelapor_nama . '</small></div>';
            $row[] = $kode_dengan_pelapor;
            $row[] = $item->judul;

            // Kategori
            if ($item->nama_kategori) {
                $kategori_badge = '<span class="label label-primary">
                                  <i class="fa fa-tag"></i> ' . $item->nama_kategori . '</span>';
            } else {
                $kategori_badge = '<span class="label label-default">Tidak ada kategori</span>';
            }
            $row[] = $kategori_badge;

            // // Unit Kerja
            // $unitkerja_badge = $item->nama_unitkerja ? '<span class="label label-info"><i class="fa fa-building"></i> ' . $item->nama_unitkerja . '</span>' : '<span class="label label-default">Tidak ada unit kerja</span>';
            // $row[] = $unitkerja_badge;

            // Status dengan label
            $status_class = '';
            switch($item->status) {
                case 'LAPOR':
                    $status_class = 'label-danger';
                    break;
                case 'DITERIMA':
                    $status_class = 'label-info';
                    break;
                case 'DIKERJAKAN':
                    $status_class = 'label-warning';
                    break;
                case 'BATAL':
                    $status_class = 'label-default';
                    break;
                case 'SELESAI':
                    $status_class = 'label-success';
                    break;
                default:
                    $status_class = 'label-default';
            }
            $row[] = '<span class="label ' . $status_class . '">' . $item->status . '</span>';

            // // Prioritas
            // $prioritas_class = '';
            // switch($item->prioritas) {
            //     case 'URGENT':
            //         $prioritas_class = 'label-danger';
            //         break;
            //     case 'TINGGI':
            //         $prioritas_class = 'label-warning';
            //         break;
            //     case 'SEDANG':
            //         $prioritas_class = 'label-info';
            //         break;
            //     case 'RENDAH':
            //         $prioritas_class = 'label-default';
            //         break;
            //     default:
            //         $prioritas_class = 'label-default';
            // }
            // $row[] = '<span class="label ' . $prioritas_class . '">' . $item->prioritas . '</span>';

            $row[] = date('d-m-Y H:i', strtotime($item->created_at));

            // Aksi
            $aksi = '';
            if ($can_detail) {
                $aksi .= '<a href="'.base_url('pelaporan/detail/'.$item->id).'" class="btn btn-sm btn-info" data-toggle="tooltip" title="Detail Laporan"><i class="fa fa-eye"></i></a> ';
            }
            if ($can_update) {
                $aksi .= '<button type="button" class="btn btn-sm btn-warning btn-edit" data-toggle="tooltip" title="Edit Laporan" data-id="'.$item->id.'"><i class="fa fa-edit"></i></button> ';
            }
            if ($can_update_status) {
                $aksi .= '<button type="button" class="btn btn-sm btn-primary btn-update-status" data-toggle="tooltip" title="Update Status" data-id="'.$item->id.'" data-status="'.$item->status.'"><i class="fa fa-refresh"></i></button> ';
            }
            if ($can_delete) {
                $aksi .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-toggle="tooltip" title="Hapus Laporan" data-id="'.$item->id.'"><i class="fa fa-trash"></i></button> ';
            }
            $aksi = trim($aksi);
            $row[] = $aksi;

            $data[] = $row;
        }

        $output = array(
            "draw" => (int)$this->input->post('draw'),
            "recordsTotal" => $this->ajax_data_m->count_all(),
            "recordsFiltered" => $this->ajax_data_m->count_filtered(),
            "data" => $data
        );

        echo json_encode($output);
    }

    public function save()
    {
        $id = $this->input->post('id');
        if (empty($id)) {
            ce_hak_akses('admin.pelaporan.add');
        } else {
            ce_hak_akses('admin.pelaporan.update');
        }
        
        $data = [
            'judul' => $this->input->post('judul'),
            'deskripsi' => $this->input->post('deskripsi'),
            'kategori' => $this->input->post('kategori'),
            'alamat' => $this->input->post('alamat'),
            'lokasi_lat' => $this->input->post('lokasi_lat'),
            'lokasi_lng' => $this->input->post('lokasi_lng'),
            'prioritas' => $this->input->post('prioritas') ?: 'SEDANG',
            'pelapor_nama' => $this->input->post('pelapor_nama'),
            'pelapor_telepon' => $this->input->post('pelapor_telepon'),
            'pelapor_nik' => $this->input->post('pelapor_nik'),
            'pelapor_alamat' => $this->input->post('pelapor_alamat'),
            'masyarakat_id' => $this->input->post('masyarakat_id') ?: null,
            'tanggal_selesai' => $this->input->post('tanggal_selesai'),
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $this->session->userdata('id_user')
        ];

        // Validasi
        $errors = $this->pelaporan_m->validate_data($data, $id);
        if (!empty($errors)) {
            json_response(false, implode('<br>', $errors));
            return;
        }

        if (empty($id)) {
            // Generate kode laporan
            $data['kode_laporan'] = $this->pelaporan_m->generate_kode_laporan();
            $data['status'] = 'LAPOR'; // Default status
            $data['unitkerja_id'] = $this->session->userdata('id_unitkerja'); // Set unit kerja dari user yang login
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['created_by'] = $this->session->userdata('id_user');
            $result = $this->pelaporan_m->insert_data($data);
            $message = 'Data laporan berhasil ditambahkan';

            // Insert history
            if ($result) {
                $laporan_id = $this->db->insert_id();
                $this->pelaporan_m->insert_history($laporan_id, null, 'LAPOR', 'Laporan baru dibuat', $this->session->userdata('id_user'));
            }
        } else {
            $result = $this->pelaporan_m->update_data($data, $id);
            $message = 'Data laporan berhasil diperbarui';
        }

        if ($result) {
            json_response(true, $message);
        } else {
            json_response(false, 'Gagal menyimpan data laporan');
        }
    }

    public function delete()
    {
        ce_hak_akses('admin.pelaporan.delete');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            json_response(false, 'ID laporan tidak valid');
            return;
        }

        $result = $this->pelaporan_m->delete_data($id);

        if ($result) {
            json_response(true, 'Data laporan berhasil dihapus');
        } else {
            json_response(false, 'Gagal menghapus data laporan');
        }
    }

    public function detail($id)
    {
        ce_hak_akses('admin.pelaporan.detail');

        $laporan = $this->pelaporan_m->get_by_id($id);
        if (!$laporan) {
            show_404();
            return;
        }

        $data['header'] = 'Detail <small>Laporan: ' . $laporan->kode_laporan . '</small>';
        $data['halaman'] = 'pelaporan/detail';
        $data['laporan'] = $laporan;
        $data['timeline'] = $this->pelaporan_m->get_laporan_timeline($id);
        $data['files'] = $this->pelaporan_m->get_laporan_files($id);
        $data['comments'] = $this->pelaporan_m->get_laporan_comments($id);

        // Enhanced analytics data
        $data['processing_stats'] = $this->pelaporan_m->get_report_processing_stats($id);
        $data['performance_comparison'] = $this->pelaporan_m->get_performance_comparison($id);
        $data['sla_compliance'] = $this->pelaporan_m->get_sla_compliance($id);

        $data['javascript'] = array(
            'pelaporan/js_detail' => null
        );
        $this->load->view('template', $data);
    }

    public function update_status()
    {
        ce_hak_akses('admin.pelaporan.update_status');
        
        $id = $this->input->post('id');
        $status_baru = $this->input->post('status');
        $keterangan = $this->input->post('keterangan');
        $tanggal_selesai = $this->input->post('tanggal_selesai');
        
        if (empty($id) || empty($status_baru)) {
            json_response(false, 'Data tidak lengkap');
            return;
        }

        // Validasi status flow
        $laporan = $this->pelaporan_m->get_by_id($id);
        if (!$laporan) {
            json_response(false, 'Laporan tidak ditemukan');
            return;
        }

        // Update status
        $result = $this->pelaporan_m->update_status($id, $status_baru, $keterangan, $tanggal_selesai, $this->session->userdata('id_user'));

        if ($result) {
            json_response(true, 'Status laporan berhasil diperbarui');
        } else {
            json_response(false, 'Gagal memperbarui status laporan');
        }
    }

    public function dashboard()
    {
        ce_hak_akses('admin.pelaporan.dashboard');

        $data['header'] = 'Dashboard <small>Pelaporan</small>';
        $data['halaman'] = 'pelaporan/dashboard';
        $data['javascript'] = array(
            'pelaporan/js_dashboard' => null
        );

        // Get user unit kerja for filtering
        $user_id_unitkerja = $this->session->userdata('id_unitkerja');
        $user_level = $this->session->userdata('id_level');

        // Filter by unit kerja if not super admin
        $unitkerja_filter = ($user_level != 1 && !empty($user_id_unitkerja)) ? $user_id_unitkerja : null;

        // Get dashboard statistics
        $data['stats'] = $this->pelaporan_m->get_dashboard_stats($unitkerja_filter);
        $data['recent_reports'] = $this->pelaporan_m->get_recent_reports(10, $unitkerja_filter);
        $data['reports_by_status'] = $this->pelaporan_m->get_reports_by_status($unitkerja_filter);
        $data['reports_by_prioritas'] = $this->pelaporan_m->get_reports_by_prioritas($unitkerja_filter);
        $data['reports_by_kategori'] = $this->pelaporan_m->get_reports_by_kategori($unitkerja_filter);
        $data['reports_map_data'] = $this->pelaporan_m->get_reports_map_data($unitkerja_filter);

        $this->load->view('template', $data);
    }

    // AJAX: Map markers with lokasi filters
     public function ajax_reports_map()
     {
         if (!$this->session->user_login) {
             $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>false,'message'=>'Unauthorized']));
             return;
         }

         // Get user unit kerja for filtering
         $user_id_unitkerja = $this->session->userdata('id_unitkerja');
         $user_level = $this->session->userdata('id_level');
         $unitkerja_filter = ($user_level != 1 && !empty($user_id_unitkerja)) ? $user_id_unitkerja : null;

         $id_kota = $this->input->post('id_kota') ?: '';
         $id_kecamatan = $this->input->post('id_kecamatan');
         $id_kelurahan = $this->input->post('id_kelurahan');
         $rows = $this->pelaporan_m->get_reports_map_data_filtered($id_kota, $id_kecamatan, $id_kelurahan, $unitkerja_filter);
         $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>true,'data'=>$rows]));
     }

    // AJAX: Reports by kategori for chart
     public function ajax_reports_by_kategori()
     {
         if (!$this->session->user_login) {
             $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>false,'message'=>'Unauthorized']));
             return;
         }

         // Get user unit kerja for filtering
         $user_id_unitkerja = $this->session->userdata('id_unitkerja');
         $user_level = $this->session->userdata('id_level');
         $unitkerja_filter = ($user_level != 1 && !empty($user_id_unitkerja)) ? $user_id_unitkerja : null;

         $rows = $this->pelaporan_m->get_reports_by_kategori($unitkerja_filter);
         $labels = [];
         $data = [];
         $colors = [];
         foreach ($rows as $r) {
             $labels[] = $r->nama_kategori ?: ($r->kategori ?: 'Tanpa Kategori');
             $data[] = (int)$r->count;
             $colors[] = $r->warna ?: '#3c8dbc';
         }
         $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>true,'labels'=>$labels,'data'=>$data,'colors'=>$colors]));
     }

    // AJAX: Reports by status for chart
     public function ajax_reports_by_status()
     {
         if (!$this->session->user_login) {
             $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>false,'message'=>'Unauthorized']));
             return;
         }

         // Get user unit kerja for filtering
         $user_id_unitkerja = $this->session->userdata('id_unitkerja');
         $user_level = $this->session->userdata('id_level');
         $unitkerja_filter = ($user_level != 1 && !empty($user_id_unitkerja)) ? $user_id_unitkerja : null;

         $rows = $this->pelaporan_m->get_reports_by_status($unitkerja_filter);
         // Order statuses predictably
         $order = ['LAPOR'=>'#f39c12','DITERIMA'=>'#3c8dbc','DIKERJAKAN'=>'#f56954','BATAL'=>'#95a5a6','SELESAI'=>'#00a65a'];
         $counts = array_fill_keys(array_keys($order), 0);
         foreach ($rows as $r) { $counts[$r->status] = (int)$r->count; }
         $labels = array_keys($order);
         $data = array_values($counts);
         $colors = array_values($order);
         $this->output->set_content_type('application/json')->set_output(json_encode(['status'=>true,'labels'=>$labels,'data'=>$data,'colors'=>$colors]));
     }

    public function get_kategori()
    {
        $kategori = $this->pelaporan_m->get_active_kategori();
        $response = [
            'status' => true,
            'data' => $kategori
        ];
        
        echo json_encode($response);
    }

    public function get_masyarakat()
    {
        $masyarakat = $this->pelaporan_m->get_active_masyarakat();
        $response = [
            'status' => true,
            'data' => $masyarakat
        ];
        
        echo json_encode($response);
    }

    public function get_masyarakat_by_id()
    {
        $id = $this->input->post('id');

        if (empty($id)) {
            json_response(false, 'ID masyarakat tidak valid');
            return;
        }

        $masyarakat = $this->pelaporan_m->get_masyarakat_by_id($id);

        if ($masyarakat) {
            json_response(true, '', $masyarakat);
        } else {
            json_response(false, 'Data masyarakat tidak ditemukan');
        }
    }

    public function get_by_id()
    {
        ce_hak_akses('admin.pelaporan.view');
        
        $id = $this->input->post('id');
        
        if (empty($id)) {
            json_response(false, 'ID laporan tidak valid');
            return;
        }

        $laporan = $this->pelaporan_m->get_by_id($id);
        
        if ($laporan) {
            json_response(true, '', $laporan);
        } else {
            json_response(false, 'Data laporan tidak ditemukan');
        }
    }

    public function save_comment()
    {
        ce_hak_akses('admin.pelaporan.update');

        $this->form_validation->set_rules('pelaporan_id', 'ID Pelaporan', 'required|numeric');
        $this->form_validation->set_rules('comment', 'Komentar', 'required|trim');
        $this->form_validation->set_rules('rating', 'Rating', 'numeric|greater_than[0]|less_than[6]');

        if ($this->form_validation->run() == FALSE) {
            json_response(false, validation_errors());
            return;
        }

        $data = array(
            'pelaporan_id' => $this->input->post('pelaporan_id'),
            'comment' => $this->input->post('comment'),
            'rating' => $this->input->post('rating') ?: null,
            'is_internal' => $this->input->post('is_internal') ? 1 : 0,
            'created_by' => $this->session->userdata('id_user'),
            'created_at' => date('Y-m-d H:i:s')
        );

        $result = $this->pelaporan_m->save_comment($data);

        if ($result) {
            json_response(true, 'Komentar berhasil disimpan');
        } else {
            json_response(false, 'Gagal menyimpan komentar');
        }
    }

    public function export()
    {
        ce_hak_akses('admin.pelaporan.export');

        $format = $this->input->get('format') ?: 'xlsx';
        $status = $this->input->get('status');
        $kategori = $this->input->get('kategori');
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');

        // Get user unit kerja for filtering
        $user_id_unitkerja = $this->session->userdata('id_unitkerja');
        $user_level = $this->session->userdata('id_level');
        $unitkerja_filter = ($user_level != 1 && !empty($user_id_unitkerja)) ? $user_id_unitkerja : null;

        $data = $this->pelaporan_m->get_export_data($status, $kategori, $start_date, $end_date, $unitkerja_filter);

        if ($format == 'xlsx' || $format == 'excel') {
            $this->export_excel($data);
        } elseif ($format == 'pdf') {
            $this->export_pdf($data);
        } else {
            show_error('Format export tidak didukung');
        }
    }

    private function export_excel($data)
    {
        // Load PhpSpreadsheet via Composer autoload
        require_once FCPATH . 'vendor/autoload.php';

        // Create new Spreadsheet object
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Get active sheet
        $sheet = $spreadsheet->getActiveSheet();

        // Set document properties
        $spreadsheet->getProperties()
            ->setCreator('Dashboard Nusakoding')
            ->setLastModifiedBy('Dashboard Nusakoding')
            ->setTitle('Data Laporan')
            ->setSubject('Export Data Laporan')
            ->setDescription('Data laporan yang diekspor dari sistem');

        // Set header
        $sheet->setCellValue('A1', 'DATA LAPORAN MASYARAKAT');
        $sheet->mergeCells('A1:N1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Set column headers
        $headers = [
            'A2' => 'No',
            'B2' => 'Kode Laporan',
            'C2' => 'Judul',
            'D2' => 'Deskripsi',
            'E2' => 'Kategori',
            'F2' => 'Status',
            'G2' => 'Prioritas',
            'H2' => 'Pelapor',
            'I2' => 'Telepon',
            'J2' => 'NIK',
            'K2' => 'Alamat',
            'L2' => 'Tanggal Dibuat',
            'M2' => 'Operator',
            'N2' => 'Unit Kerja'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
            $sheet->getStyle($cell)->getFill()->getStartColor()->setARGB('FFE6E6FA');
        }

        // Add data
        $row = 3;
        $no = 1;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $item->kode_laporan);
            $sheet->setCellValue('C' . $row, $item->judul);
            $sheet->setCellValue('D' . $row, $item->deskripsi);
            $sheet->setCellValue('E' . $row, $item->nama_kategori ?: '-');
            $sheet->setCellValue('F' . $row, $item->status);
            $sheet->setCellValue('G' . $row, $item->prioritas);
            $sheet->setCellValue('H' . $row, $item->pelapor_nama);
            $sheet->setCellValue('I' . $row, $item->pelapor_telepon ?: '-');
            $sheet->setCellValue('J' . $row, $item->pelapor_nik ?: '-');
            $sheet->setCellValue('K' . $row, $item->alamat);
            $sheet->setCellValue('L' . $row, date('d/m/Y H:i', strtotime($item->created_at)));
            $sheet->setCellValue('M' . $row, $item->operator_nama ?: '-');
            $sheet->setCellValue('N' . $row, $item->nama_unitkerja ?: '-');
            $row++;
        }

        // Auto size columns
        foreach (range('A', 'N') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set borders
        $styleArray = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ];
        $sheet->getStyle('A2:N' . ($row - 1))->applyFromArray($styleArray);

        // Create filename
        $filename = 'data_laporan_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        // Save to output
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        exit;
    }

    private function export_pdf($data)
    {
        // Load PDF library
        $this->load->library('dompdf_lib');

        // Prepare HTML content
        $html = '<h2 style="text-align: center;">DATA LAPORAN MASYARAKAT</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead>';
        $html .= '<tr style="background-color: #f0f0f0;">';
        $html .= '<th>No</th>';
        $html .= '<th>Kode Laporan</th>';
        $html .= '<th>Judul</th>';
        $html .= '<th>Kategori</th>';
        $html .= '<th>Status</th>';
        $html .= '<th>Prioritas</th>';
        $html .= '<th>Pelapor</th>';
        $html .= '<th>Tanggal Dibuat</th>';
        $html .= '<th>Unit Kerja</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        $no = 1;
        foreach ($data as $item) {
            $html .= '<tr>';
            $html .= '<td>' . $no++ . '</td>';
            $html .= '<td>' . $item->kode_laporan . '</td>';
            $html .= '<td>' . $item->judul . '</td>';
            $html .= '<td>' . ($item->nama_kategori ?: '-') . '</td>';
            $html .= '<td>' . $item->status . '</td>';
            $html .= '<td>' . $item->prioritas . '</td>';
            $html .= '<td>' . $item->pelapor_nama . '</td>';
            $html .= '<td>' . date('d/m/Y H:i', strtotime($item->created_at)) . '</td>';
            $html .= '<td>' . ($item->nama_unitkerja ?: '-') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';

        // Generate PDF
        $this->dompdf_lib->loadHtml($html);
        $this->dompdf_lib->setPaper('A4', 'landscape');
        $this->dompdf_lib->render();

        // Output PDF
        $filename = 'data_laporan_' . date('Y-m-d_H-i-s') . '.pdf';
        $this->dompdf_lib->stream($filename, array('Attachment' => true));
        exit;
    }

    public function upload_foto_progress()
    {
        ce_hak_akses('admin.pelaporan.update');

        $pelaporan_id = $this->input->post('pelaporan_id');

        if (!$pelaporan_id) {
            json_response(false, 'ID Pelaporan tidak valid');
            return;
        }

        // Configure upload
        $config['upload_path'] = './uploads/pelaporan/progress/';
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = 5120; // 5MB
        $config['encrypt_name'] = TRUE;

        // Create directory if not exists
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0755, true);
        }

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if ($this->upload->do_upload('foto')) {
            $upload_data = $this->upload->data();

            // Save to database
            $data = array(
                'pelaporan_id' => $pelaporan_id,
                'file_name' => $upload_data['orig_name'],
                'file_path' => 'uploads/pelaporan/progress/' . $upload_data['file_name'],
                'file_size' => $upload_data['file_size'],
                'file_type' => 'progress_photo',
                'uploaded_by' => $this->session->userdata('id_user'),
                'uploaded_at' => date('Y-m-d H:i:s')
            );

            $file_id = $this->pelaporan_m->save_file($data);

            if ($file_id) {
                json_response(true, 'Foto berhasil diupload', array('file_id' => $file_id));
                return;
            } else {
                // Delete uploaded file if database save failed
                unlink($config['upload_path'] . $upload_data['file_name']);
                json_response(false, 'Gagal menyimpan data foto');
                return;
            }
        } else {
            json_response(false, $this->upload->display_errors('', ''));
            return;
        }
    }

    public function get_uploaded_photos()
    {
        ce_hak_akses('admin.pelaporan.view');

        $pelaporan_id = $this->input->post('pelaporan_id');
        $photos = $this->pelaporan_m->get_progress_photos($pelaporan_id);

        json_response(true, '', $photos);
    }

    public function delete_foto_progress()
    {
        ce_hak_akses('admin.pelaporan.update');

        $id = $this->input->post('id');
        $file = $this->pelaporan_m->get_file_by_id($id);

        if ($file) {
            // Delete file from server
            if (file_exists($file->file_path)) {
                unlink($file->file_path);
            }

            // Delete from database
            $result = $this->pelaporan_m->delete_file($id);

            if ($result) {
                json_response(true, 'Foto berhasil dihapus');
            } else {
                json_response(false, 'Gagal menghapus foto dari database');
            }
        } else {
            json_response(false, 'File tidak ditemukan');
        }
    }
}
