<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kepengurusan_sosial_m extends CI_Model
{
    private $table = 'kepengurusan_sosial';

    public function kepengurusan_sosial_get_all()
    {
        return $this->db->order_by('created_at', 'desc')->get($this->table)->result();
    }
    
    public function kepengurusan_sosial_get_active()
    {
        return $this->db->where('status', 1)->order_by('nama_lengkap', 'asc')->get($this->table)->result();
    }
    
    public function kepengurusan_sosial_by_id($id)
    {
        return $this->db->where('id', $id)->get($this->table)->row();
    }
    
    public function kepengurusan_sosial_by_id_array($id)
    {
        return $this->db->where('id', $id)->get($this->table)->result();
    }
    
    public function kepengurusan_sosial_insert_data($post_data)
    {
        return $this->db->insert($this->table, $post_data);
    }
    
    public function kepengurusan_sosial_update_data($post_data, $id)
    {
        return $this->db->where('id', $id)->update($this->table, $post_data);
    }
    
    public function kepengurusan_sosial_delete_data($id)
    {
        return $this->db->where('id', $id)->delete($this->table);
    }
    
    public function kepengurusan_sosial_by_nik($nik)
    {
        return $this->db->where('nik_ktp', $nik)->get($this->table)->row();
    }

    public function kepengurusan_sosial_by_no_wa($no_wa)
    {
        return $this->db->where('no_wa_hp', $no_wa)->get($this->table)->row();
    }
    
    public function kepengurusan_sosial_by_jenis_kelamin($jenis_kelamin)
    {
        return $this->db->where('jenis_kelamin', $jenis_kelamin)->get($this->table)->result();
    }
    
    public function kepengurusan_sosial_by_agama($agama)
    {
        return $this->db->where('agama', $agama)->get($this->table)->result();
    }
    
    public function kepengurusan_sosial_by_status_kawin($status_kawin)
    {
        return $this->db->where('status_kawin', $status_kawin)->get($this->table)->result();
    }

    public function check_nik_exists($nik, $exclude_id = null)
    {
        $this->db->where('nik_ktp', $nik);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    public function check_no_wa_exists($no_wa, $exclude_id = null)
    {
        $this->db->where('no_wa_hp', $no_wa);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Count methods for statistics
    public function count_all_kepengurusan()
    {
        return $this->db->count_all_results($this->table);
    }

    public function count_by_jenis_kelamin($jenis_kelamin)
    {
        return $this->db->where('jenis_kelamin', $jenis_kelamin)->count_all_results($this->table);
    }

    public function count_by_agama($agama)
    {
        return $this->db->where('agama', $agama)->count_all_results($this->table);
    }

    public function count_by_status_kawin($status_kawin)
    {
        return $this->db->where('status_kawin', $status_kawin)->count_all_results($this->table);
    }

    public function count_by_pendidikan($pendidikan)
    {
        return $this->db->where('pendidikan_terakhir', $pendidikan)->count_all_results($this->table);
    }

    public function get_agama_list()
    {
        return $this->db->select('agama')
                        ->distinct()
                        ->where('agama !=', '')
                        ->order_by('agama', 'asc')
                        ->get($this->table)->result();
    }

    public function get_pendidikan_list()
    {
        return $this->db->select('pendidikan_terakhir')
                        ->distinct()
                        ->where('pendidikan_terakhir !=', '')
                        ->order_by('pendidikan_terakhir', 'asc')
                        ->get($this->table)->result();
    }

    // Validation method
    public function validate_kepengurusan_data($data, $id = null)
    {
        $errors = [];

        // Validasi nama lengkap
        if (empty($data['nama_lengkap'])) {
            $errors[] = 'Nama lengkap tidak boleh kosong';
        }

        // Validasi NIK KTP
        if (empty($data['nik_ktp'])) {
            $errors[] = 'NIK KTP tidak boleh kosong';
        } elseif (strlen($data['nik_ktp']) != 16) {
            $errors[] = 'NIK KTP harus 16 digit';
        } elseif (!is_numeric($data['nik_ktp'])) {
            $errors[] = 'NIK KTP harus berupa angka';
        }

        // Validasi NO KK (jika diisi)
        if (!empty($data['no_kk'])) {
            if (strlen($data['no_kk']) != 16) {
                $errors[] = 'Nomor KK harus 16 digit';
            } elseif (!is_numeric($data['no_kk'])) {
                $errors[] = 'Nomor KK harus berupa angka';
            } elseif ($this->check_no_kk_exists($data['no_kk'], $id)) {
                $errors[] = 'Nomor KK sudah terdaftar';
            }
        }

        // Validasi NO WA/HP
        if (empty($data['no_wa_hp'])) {
            $errors[] = 'Nomor WA/HP tidak boleh kosong';
        }

        // Check for duplicate NIK
        if (!empty($data['nik_ktp'])) {
            if ($this->check_nik_exists($data['nik_ktp'], $id)) {
                $errors[] = 'NIK KTP sudah terdaftar';
            }
        }

        return $errors;
    }

    public function check_no_kk_exists($no_kk, $exclude_id = null)
    {
        $this->db->where('no_kk', $no_kk);
        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }
        return $this->db->get($this->table)->num_rows() > 0;
    }

    // Get kepengurusan with location info
    public function get_kepengurusan_with_location($id)
    {
        $this->db->select('ks.*, kc.nama_kecamatan, kl.nama_kelurahan');
        $this->db->from($this->table . ' ks');
        $this->db->join('kecamatan kc', 'ks.kecamatan = kc.id_kecamatan', 'left');
        $this->db->join('kelurahan kl', 'ks.kelurahan = kl.id_kelurahan', 'left');
        $this->db->where('ks.id', $id);
        return $this->db->get()->row();
    }

    public function get_kepengurusan_for_select()
    {
        $this->db->select('id, nama_lengkap');
        $this->db->where('status', 1);
        $this->db->order_by('nama_lengkap', 'asc');
        $result = $this->db->get($this->table)->result();

        $options = [];
        foreach ($result as $row) {
            $options[$row->id] = $row->nama_lengkap;
        }

        return $options;
    }

    public function import_excel_data($data)
    {
        $this->load->library('upload');

        // Load PHPSpreadsheet
        require_once APPPATH . '../vendor/autoload.php';

        try {
            $inputFileName = $_FILES['excel_file']['tmp_name'];

            // Load file Excel
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($inputFileName);
            $worksheet = $spreadsheet->getActiveSheet();

            $imported = 0;
            $skipped = 0;
            $errors = [];
            $failed_data = [];
            $rowNumber = 1; // Skip header row

            // Loop through each row
            foreach ($worksheet->getRowIterator() as $row) {
                $rowNumber++;
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Skip header row
                if ($rowNumber == 2) continue;

                // Parse data from Excel
                $nama_lengkap = trim($cells[0] ?? '');
                $nik_ktp = trim($cells[1] ?? '');
                $no_kk = trim($cells[2] ?? '');
                $no_wa_hp = trim($cells[3] ?? '');
                $tempat_lahir = trim($cells[4] ?? '');
                $tanggal_lahir = trim($cells[5] ?? '');
                $jenis_kelamin = trim($cells[6] ?? '');
                $agama = trim($cells[7] ?? '');
                $alamat = trim($cells[8] ?? '');
                $kecamatan = trim($cells[9] ?? '');
                $kelurahan = trim($cells[10] ?? '');
                $status_kawin = trim($cells[11] ?? '');
                $pendidikan_terakhir = trim($cells[12] ?? '');

                // Skip empty rows
                if (empty($nama_lengkap) && empty($nik_ktp)) continue;

                // Validate required fields
                $validation_errors = [];
                if (empty($nama_lengkap)) {
                    $validation_errors[] = "Nama lengkap tidak boleh kosong";
                }
                if (empty($nik_ktp)) {
                    $validation_errors[] = "NIK KTP tidak boleh kosong";
                }

                // Validate NIK format
                if (!empty($nik_ktp)) {
                    if (strlen($nik_ktp) != 16) {
                        $validation_errors[] = "NIK KTP harus 16 digit";
                    } elseif (!is_numeric($nik_ktp)) {
                        $validation_errors[] = "NIK KTP harus berupa angka";
                    }
                }

                // Validate No KK format if provided
                if (!empty($no_kk)) {
                    if (strlen($no_kk) != 16) {
                        $validation_errors[] = "No KK harus 16 digit";
                    } elseif (!is_numeric($no_kk)) {
                        $validation_errors[] = "No KK harus berupa angka";
                    }
                }

                // Check for validation errors
                if (!empty($validation_errors)) {
                    $skipped++;
                    $failed_data[] = [
                        'row' => $rowNumber,
                        'nama_lengkap' => $nama_lengkap,
                        'nik_ktp' => $nik_ktp,
                        'no_kk' => $no_kk,
                        'errors' => $validation_errors
                    ];
                    $errors[] = "Baris {$rowNumber} ({$nama_lengkap}): " . implode(', ', $validation_errors);
                    continue;
                }

                // Check for duplicate NIK
                if (!empty($nik_ktp) && $this->check_nik_exists($nik_ktp)) {
                    $skipped++;
                    $failed_data[] = [
                        'row' => $rowNumber,
                        'nama_lengkap' => $nama_lengkap,
                        'nik_ktp' => $nik_ktp,
                        'no_kk' => $no_kk,
                        'errors' => ['NIK KTP sudah terdaftar']
                    ];
                    $errors[] = "Baris {$rowNumber} ({$nama_lengkap}): NIK {$nik_ktp} sudah terdaftar";
                    continue;
                }

                // Check for duplicate No KK
                if (!empty($no_kk) && $this->check_no_kk_exists($no_kk)) {
                    $skipped++;
                    $failed_data[] = [
                        'row' => $rowNumber,
                        'nama_lengkap' => $nama_lengkap,
                        'nik_ktp' => $nik_ktp,
                        'no_kk' => $no_kk,
                        'errors' => ['No KK sudah terdaftar']
                    ];
                    $errors[] = "Baris {$rowNumber} ({$nama_lengkap}): No KK {$no_kk} sudah terdaftar";
                    continue;
                }

                // Prepare data
                $insert_data = [
                    'nama_lengkap' => $nama_lengkap,
                    'nik_ktp' => $nik_ktp,
                    'no_kk' => $no_kk,
                    'no_wa_hp' => $no_wa_hp,
                    'tempat_lahir' => $tempat_lahir,
                    'tanggal_lahir' => $tanggal_lahir ? date('Y-m-d', strtotime($tanggal_lahir)) : null,
                    'jenis_kelamin' => $jenis_kelamin,
                    'agama' => $agama,
                    'alamat' => $alamat,
                    'kecamatan' => $kecamatan,
                    'kelurahan' => $kelurahan,
                    'status_kawin' => $status_kawin,
                    'pendidikan_terakhir' => $pendidikan_terakhir,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_by' => $this->session->userdata('user_id'),
                    'updated_by' => $this->session->userdata('user_id')
                ];

                // Insert data
                if ($this->db->insert($this->table, $insert_data)) {
                    $imported++;
                } else {
                    $skipped++;
                    $failed_data[] = [
                        'row' => $rowNumber,
                        'nama_lengkap' => $nama_lengkap,
                        'nik_ktp' => $nik_ktp,
                        'no_kk' => $no_kk,
                        'errors' => ['Gagal menyimpan data ke database']
                    ];
                    $errors[] = "Baris {$rowNumber} ({$nama_lengkap}): Gagal menyimpan data ke database";
                }
            }

            return [
                'success' => true,
                'imported' => $imported,
                'skipped' => $skipped,
                'errors' => $errors,
                'failed_data' => $failed_data,
                'total_processed' => $imported + $skipped
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error processing Excel file: ' . $e->getMessage()
            ];
        }
    }

    // ========== FRONTEND PUBLIC METHODS ==========

    /**
     * Count all kepengurusan for frontend
     */
    public function count_all()
    {
        return $this->db->where('status', 1)->count_all_results($this->table);
    }

    /**
     * Count public data with filters
     */
    public function count_public_data($kategori_id = '', $search = '')
    {
        $this->db->from($this->table . ' ks');
        $this->db->join('kepengurusan_detail kd', 'ks.id = kd.kepengurusan_sosial_id', 'left');
        $this->db->join('kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left');
        $this->db->where('ks.status', 1);

        if ($kategori_id) {
            $this->db->where('kd.kategori_kepengurusan_id', $kategori_id);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('ks.nama_lengkap', $search);
            $this->db->or_like('ks.alamat', $search);
            $this->db->or_like('kk.kepengurusan_nama', $search);
            $this->db->group_end();
        }

        return $this->db->count_all_results();
    }

    /**
     * Get public data with pagination and filters
     */
    public function get_public_data($limit, $offset, $kategori_id = '', $search = '')
    {
        $this->db->select('ks.*, kd.tanggal_sk, kd.nomor_sk, kd.masa_jabatan, kk.kepengurusan_nama as nama_kategori, kc.nama_kecamatan, kl.nama_kelurahan');
        $this->db->from($this->table . ' ks');
        $this->db->join('kepengurusan_detail kd', 'ks.id = kd.kepengurusan_sosial_id', 'left');
        $this->db->join('kategori_kepengurusan kk', 'kd.kategori_kepengurusan_id = kk.kepengurusan_id', 'left');
        $this->db->join('kecamatan kc', 'ks.kecamatan = kc.id_kecamatan', 'left');
        $this->db->join('kelurahan kl', 'ks.kelurahan = kl.id_kelurahan', 'left');
        $this->db->where('ks.status', 1);

        if ($kategori_id) {
            $this->db->where('kd.kategori_kepengurusan_id', $kategori_id);
        }

        if ($search) {
            $this->db->group_start();
            $this->db->like('ks.nama_lengkap', $search);
            $this->db->or_like('ks.alamat', $search);
            $this->db->or_like('kk.kepengurusan_nama', $search);
            $this->db->group_end();
        }

        $this->db->order_by('ks.nama_lengkap', 'asc');
        $this->db->limit($limit, $offset);

        return $this->db->get()->result();
    }
}
