<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Filemanager extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('filemanager_m');
        $this->load->helper('secure_file_helper');
    }

    private function _check_auth($permission = '')
    {
        if (is_cli()) {
            return;
        }
        if (!$this->session->user_login && !$this->session->mahasiswa_logged_in) {
            redirect('user/login');
            exit;
        }
        if ($permission && !ce_hak_akses($permission, false) && $this->session->userdata('id_level') != 1) {
            show_404();
            exit;
        }
        if ($permission) {
            ce_active_menu($permission);
        }
    }

    public function index()
    {
        $this->_check_auth('admin.filemanager.view');
        
        $data['header'] = 'File Manager';
        $data['halaman'] = 'filemanager/filemanager';
        $data['javascript'] = array('filemanager/js_filemanager' => null);
        $this->load->view('template', $data);
    }

    public function ajax_data()
    {
        $this->_check_auth('admin.filemanager.view');

        $this->load->helper('directory');
        
        // Mendapatkan direktori saat ini dari parameter
        $current_dir = $this->input->post('directory') ? $this->input->post('directory') : 'uploads';
        
        // Decode direktori jika dalam format base64
        if (base64_decode($current_dir, true)) {
            $decoded = base64_decode($current_dir);
            if (strpos($decoded, 'uploads') === 0) {
                $current_dir = $decoded;
            }
        }
        
        // Pastikan direktori diakhiri dengan slash
        if (substr($current_dir, -1) != '/') {
            $current_dir .= '/';
        }
        
        // Pastikan direktori dimulai dari uploads untuk keamanan
        if (strpos($current_dir, 'uploads') !== 0) {
            $current_dir = 'uploads/';
        }
        
        // Simpan path asli di session untuk keamanan
        $this->session->set_userdata('current_directory', $current_dir);
        
        // Buat breadcrumb untuk navigasi
        $breadcrumb = $this->_generate_breadcrumb($current_dir);
        
        $map = directory_map($current_dir, 1);

        $data = array();
        $no = $this->input->post('start');
        
        // Tambahkan tombol kembali jika bukan di root uploads
        if ($current_dir != 'uploads/') {
            $no++;
            $row = array();
            $row[] = $no;
            $row[] = '<i class="fa fa-level-up"></i>';
            $row[] = '..';
            
            // Dapatkan direktori parent
            $parent_dir = dirname($current_dir);
            if ($parent_dir == '.') $parent_dir = 'uploads';
            
            // Gunakan ID aman untuk navigasi
            $parent_id = base64_encode($parent_dir);
            $row[] = '<button class="btn btn-xs btn-default btn-navigate" data-dir="'.$parent_id.'"><i class="fa fa-folder-open"></i> Kembali</button>';
            $data[] = $row;
        }
        
        if(is_array($map)){
            foreach ($map as $item) {
                $no++;
                $row = array();
                $row[] = $no;
                
                $full_path = $current_dir . $item;
                
                if (is_dir($full_path)) {
                    // Ini adalah direktori
                    $row[] = '<i class="fa fa-folder"></i>';
                    $dir_name = rtrim($item, '/');
                    $row[] = $dir_name;
                    
                    // Gunakan ID aman untuk navigasi
                    $dir_id = base64_encode($current_dir.$dir_name);
                    $row[] = '
                        <button class="btn btn-xs btn-primary btn-navigate" data-dir="'.$dir_id.'"><i class="fa fa-folder-open"></i> Buka</button>
                        <button class="btn btn-xs btn-danger btn-delete-directory" data-dir="'.$dir_id.'" data-name="'.$dir_name.'"><i class="fa fa-trash"></i> Hapus</button>
                    ';
                } else {
                    // Ini adalah file
                    $file_ext = pathinfo($item, PATHINFO_EXTENSION);
                    $icon = $this->_get_file_icon($file_ext);
                    $row[] = $icon;
                    $row[] = $item;
                    
                    // Buat URL aman untuk file
                    $file_id = md5($current_dir . $item);
                    $this->session->set_userdata('file_' . $file_id, $current_dir . $item);
                    
                    $row[] = '
                        <button class="btn btn-xs btn-info btn-preview-file" data-id="'.$file_id.'" data-name="'.$item.'"><i class="fa fa-eye"></i> Lihat</button>
                        <a href="'.site_url('filemanager/download_file/'.$file_id).'" class="btn btn-xs btn-success"><i class="fa fa-download"></i> Download</a>
                        <button class="btn btn-xs btn-danger btn-delete-file" data-id="'.$file_id.'"><i class="fa fa-trash"></i> Hapus</button>
                    ';
                }
                
                $data[] = $row;
            }
        }

        $output = array(
            "draw" => $this->input->post('draw'),
            "recordsTotal" => count($data),
            "recordsFiltered" => count($data),
            "data" => $data,
            "current_dir_id" => base64_encode($current_dir),
            "breadcrumb" => $breadcrumb,
            "
            "
        );
        
        echo json_encode($output);
    }
    
    /**
     * Menghasilkan breadcrumb untuk navigasi direktori
     */
    private function _generate_breadcrumb($current_dir)
    {
        $parts = explode('/', rtrim($current_dir, '/'));
        $breadcrumb = [];
        $path = '';
        
        foreach ($parts as $part) {
            if (!empty($part)) {
                $path .= $part . '/';
                $breadcrumb[] = [
                    'name' => $part,
                    'path' => base64_encode(rtrim($path, '/'))
                ];
            }
        }
        
        return $breadcrumb;
    }

    /**
     * Mendapatkan ikon berdasarkan ekstensi file
     */
    private function _get_file_icon($ext)
    {
        $ext = strtolower($ext);
        
        $icons = [
            'pdf' => '<i class="fa fa-file-pdf-o text-danger"></i>',
            'doc' => '<i class="fa fa-file-word-o text-primary"></i>',
            'docx' => '<i class="fa fa-file-word-o text-primary"></i>',
            'xls' => '<i class="fa fa-file-excel-o text-success"></i>',
            'xlsx' => '<i class="fa fa-file-excel-o text-success"></i>',
            'ppt' => '<i class="fa fa-file-powerpoint-o text-warning"></i>',
            'pptx' => '<i class="fa fa-file-powerpoint-o text-warning"></i>',
            'jpg' => '<i class="fa fa-file-image-o text-info"></i>',
            'jpeg' => '<i class="fa fa-file-image-o text-info"></i>',
            'png' => '<i class="fa fa-file-image-o text-info"></i>',
            'gif' => '<i class="fa fa-file-image-o text-info"></i>',
            'zip' => '<i class="fa fa-file-archive-o text-muted"></i>',
            'rar' => '<i class="fa fa-file-archive-o text-muted"></i>',
            'txt' => '<i class="fa fa-file-text-o"></i>',
        ];
        
        return isset($icons[$ext]) ? $icons[$ext] : '<i class="fa fa-file-o"></i>';
    }

    /**
     * Mendapatkan path file dari ID aman
     */
    private function _get_file_path_from_id($file_id)
    {
        $file_path = $this->session->userdata('file_' . $file_id);
        if (!$file_path) {
            return false;
        }
        return $file_path;
    }
    
    /**
     * Mendapatkan direktori dari ID aman
     */
    private function _get_directory_from_id($dir_id)
    {
        $directory = base64_decode($dir_id);
        if (!$directory || strpos($directory, 'uploads') !== 0) {
            return 'uploads';
        }
        return $directory;
    }

    public function view_file($file_id)
    {
        // Izinkan akses jika admin atau mahasiswa sudah login
        if (!$this->session->user_login && !$this->session->mahasiswa_logged_in) {
            redirect('user/login');
            return;
        }
        
        $file_path = $this->_get_file_path_from_id($file_id);
        if (!$file_path || !file_exists($file_path)) {
            show_404();
            return;
        }
        
        $file_info = pathinfo($file_path);
        $file_ext = strtolower($file_info['extension']);
        
        // Cek apakah file adalah gambar yang dapat ditampilkan langsung
        $image_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $image_types)) {
            // Tampilkan gambar
            $this->load->helper('file');
            $mime_type = get_mime_by_extension($file_path);
            if (!$mime_type) {
                // Fallback untuk mime type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $file_path);
                finfo_close($finfo);
            }
            header('Content-Type: ' . $mime_type);
            header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            // Untuk file lain, gunakan Google Docs Viewer atau tampilkan sebagai download
            $data['file_id'] = $file_id;
            $data['file_name'] = basename($file_path);
            $data['file_ext'] = $file_ext;
            $data['header'] = 'Preview File: ' . basename($file_path);
            $data['halaman'] = 'filemanager/preview_file';
            $this->load->view('template', $data);
        }
    }
    
    public function download_file($file_id)
    {
        $this->_check_auth('admin.filemanager.view');
        
        $file_path = $this->_get_file_path_from_id($file_id);
        if (!$file_path || !file_exists($file_path)) {
            show_404();
            return;
        }
        
        $this->load->helper('download');
        force_download($file_path, NULL);
    }

    public function upload_file()
    {
        $this->_check_auth('admin.filemanager.add');
        
        // Mendapatkan direktori tujuan dari ID aman
        $dir_id = $this->input->post('dir');
        $dir = $this->_get_directory_from_id($dir_id);
        
        // Pastikan direktori dimulai dari uploads untuk keamanan
        if (strpos($dir, 'uploads') !== 0) {
            $dir = 'uploads';
        }
        
        $path = './' . $dir;
        
        // Pastikan direktori ada
        if (!is_dir($path)) {
            mkdir($path, 0755, TRUE);
        }

        $config['upload_path'] = $path;
        $config['allowed_types'] = 'gif|jpg|jpeg|png|pdf|doc|docx|xls|xlsx|ppt|pptx|zip|rar|txt';
        $config['max_size'] = 10240; // 10MB
        $config['file_name'] = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $_FILES['file']['name']);

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('file')) {
            $response = [
                'status' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        } else {
            $upload_data = $this->upload->data();
            
            $response = [
                'status' => true,
                'message' => 'File berhasil diunggah.',
                'file_name' => $upload_data['file_name'],
                'file_path' => $dir . '/' . $upload_data['file_name']
            ];
        }
        
        $response['csrf_token_name'] = $this->security->get_csrf_token_name();
        $response['csrf_hash'] = $this->security->get_csrf_hash();
        
        echo json_encode($response);
    }

    public function delete_file()
    {
        $this->_check_auth('admin.filemanager.delete');
        
        $file_id = $this->input->post('id');
        $file_path = $this->_get_file_path_from_id($file_id);
        
        if (!$file_path) {
            $response = [
                'status' => false,
                'message' => 'File tidak ditemukan.'
            ];
        } else {
            $full_path = './' . $file_path;
            
            if (file_exists($full_path) && is_file($full_path)) {
                if (unlink($full_path)) {
                    // Hapus data file dari session
                    $this->session->unset_userdata('file_' . $file_id);
                    
                    $response = [
                        'status' => true,
                        'message' => 'File berhasil dihapus.'
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'Gagal menghapus file.'
                    ];
                }
            } else {
                $response = [
                    'status' => false,
                    'message' => 'File tidak ditemukan.'
                ];            }
        }
        
        echo json_encode($response);
    }

    public function create_folder()
    {
        $this->_check_auth('admin.filemanager.add');
        
        $parent_dir_id = $this->input->post('parent_dir');
        $parent_dir = $this->_get_directory_from_id($parent_dir_id);
        $folder_name = $this->input->post('folder_name');
        
        // Validasi nama folder
        if (empty($folder_name) || !preg_match('/^[a-zA-Z0-9_-]+$/', $folder_name)) {
            $response = [
                'status' => false,
                'message' => 'Nama folder tidak valid. Gunakan hanya huruf, angka, underscore dan dash.'
            ];
        } else {
            // Pastikan direktori dimulai dari uploads untuk keamanan
            if (strpos($parent_dir, 'uploads') !== 0) {
                $parent_dir = 'uploads';
            }
            
            $new_folder_path = $parent_dir . '/' . $folder_name;
            
            if (is_dir($new_folder_path)) {
                $response = [
                    'status' => false,
                    'message' => 'Folder dengan nama tersebut sudah ada.'
                ];
            } else {
                if (mkdir($new_folder_path, 0755)) {
                    $response = [
                        'status' => true,
                        'message' => 'Folder berhasil dibuat.'
                    ];
                } else {
                    $response = [
                        'status' => false,
                        'message' => 'Gagal membuat folder.'
                    ];
                }
            }
        }
        
        echo json_encode($response);
    }

    /**
     * Menghapus direktori beserta isinya
     */
    public function delete_directory()
    {
        $this->_check_auth('admin.filemanager.delete');
        
        $dir_id = $this->input->post('dir_id');
        $directory = $this->_get_directory_from_id($dir_id);
        
        // Pastikan direktori dimulai dari uploads untuk keamanan
        if (strpos($directory, 'uploads') !== 0) {
            $response = [
                'status' => false,
                'message' => 'Direktori tidak valid.'
            ];
        } else {
            // Cek apakah direktori ada
            if (!is_dir($directory)) {
                $response = [
                    'status' => false,
                    'message' => 'Direktori tidak ditemukan.'
                ];
            } else {
                // Jangan izinkan hapus direktori root uploads
                if ($directory == 'uploads' || $directory == 'uploads/') {
                    $response = [
                        'status' => false,
                        'message' => 'Tidak dapat menghapus direktori root.'
                    ];
                } else {
                    // Hapus direktori dan semua isinya
                    if ($this->_delete_directory_recursive($directory)) {
                        $response = [
                            'status' => true,
                            'message' => 'Direktori berhasil dihapus.'
                        ];
                    } else {
                        $response = [
                            'status' => false,
                            'message' => 'Gagal menghapus direktori.'
                        ];
                    }
                }
            }
        }
        
        echo json_encode($response);
    }
    
    /**
     * Helper function untuk menghapus direktori secara rekursif
     */
    private function _delete_directory_recursive($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                // Rekursif hapus subdirektori
                $this->_delete_directory_recursive($path);
            } else {
                // Hapus file
                unlink($path);
            }
        }
        
        // Hapus direktori yang sudah kosong
        return rmdir($dir);
    }

    public function serve()
    {
        // Ambil parameter dari URL
        $file_id = $this->input->get('id');
        $expires = $this->input->get('expires');
        $signature = $this->input->get('signature');
        
        // Validasi parameter
        if (!$file_id || !$expires || !$signature) {
            show_404();
            return;
        }
        
        // Validasi signature
        if (!validate_secure_url($file_id, $expires, $signature)) {
            show_error('URL tidak valid atau sudah kadaluarsa', 403);
            return;
        }
        
        // Ambil file path dari session berdasarkan ID
        $file_path = $this->session->userdata('secure_file_' . $file_id);
        
        if (!$file_path || !file_exists($file_path)) {
            show_404();
            return;
        }
        
        // Tentukan content type menggunakan CodeIgniter's mime helper
        $this->load->helper('file');
        $mime_type = get_mime_by_extension($file_path);
        if (!$mime_type) {
            // Fallback untuk mime type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file_path);
            finfo_close($finfo);
        }
        $file_name = basename($file_path);
        
        // Set header yang sesuai
        header('Content-Type: ' . $mime_type);
        
        // Untuk gambar, tampilkan inline. Untuk file lain, download
        $image_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($mime_type, $image_types)) {
            header('Content-Disposition: inline; filename="' . $file_name . '"');
        } else {
            header('Content-Disposition: attachment; filename="' . $file_name . '"');
        }
        
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=3600');
        
        readfile($file_path);
        exit;
    }

    /**
     * Menghasilkan URL aman untuk file
     */
    public function get_secure_url()
    {
        $this->_check_auth('admin.filemanager.view');
        
        $file_id = $this->input->post('file_id');
        $file_path = $this->_get_file_path_from_id($file_id);
        if (!$file_path || !file_exists($file_path)) {
            $response = [
                'status' => false,
                'message' => 'File tidak ditemukan.'
            ];
        } else {
            // Generate unique ID untuk file ini
            $secure_id = 'fm_' . md5($file_id . time() . rand(1000, 9999));
            
            // Simpan mapping ID ke file path di session
            $this->session->set_userdata('secure_file_' . $secure_id, $file_path);
            
            // Generate URL aman dengan helper
            $secure_url = secure_file_url($secure_id, 60); // URL valid selama 60 menit
            
            $response = [
                'status' => true,
                'secure_url' => $secure_url
            ];
        }
        
        echo json_encode($response);
    }
}
