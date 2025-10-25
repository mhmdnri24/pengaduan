<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PhpSpreadsheet Library
 * 
 * Library wrapper untuk PhpSpreadsheet
 */
class PhpSpreadsheet {
    
    /**
     * Constructor
     */
    public function __construct()
    {
        // Load Composer autoload
        require_once FCPATH . 'vendor/autoload.php';
    }
    
    /**
     * Load file Excel
     * 
     * @param string $file_path Path file Excel
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function load($file_path)
    {
        return \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
    }
    
    /**
     * Baca data dari worksheet
     * 
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $worksheet Worksheet
     * @param int $start_row Baris awal (default: 1)
     * @param bool $has_header Apakah memiliki header (default: true)
     * @return array Data dari worksheet
     */
    public function read_worksheet($worksheet, $start_row = 1, $has_header = true)
    {
        $data = [];
        $headers = [];
        
        $highest_row = $worksheet->getHighestRow();
        $highest_column = $worksheet->getHighestColumn();
        $highest_column_index = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highest_column);
        
        // Baca header jika ada
        if ($has_header) {
            for ($col = 1; $col <= $highest_column_index; $col++) {
                $headers[] = $worksheet->getCellByColumnAndRow($col, $start_row)->getValue();
            }
            $start_row++;
        }
        
        // Baca data
        for ($row = $start_row; $row <= $highest_row; $row++) {
            $row_data = [];
            
            for ($col = 1; $col <= $highest_column_index; $col++) {
                $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
                
                if ($has_header) {
                    $row_data[$headers[$col - 1]] = $value;
                } else {
                    $row_data[] = $value;
                }
            }
            
            if (!empty(array_filter($row_data))) {
                $data[] = $row_data;
            }
        }
        
        return $data;
    }
    
    /**
     * Buat file Excel baru
     * 
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function create()
    {
        return new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    }
    
    /**
     * Simpan file Excel
     * 
     * @param \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet Spreadsheet
     * @param string $file_path Path file Excel
     * @param string $format Format file (xlsx, xls, csv, dll)
     * @return bool
     */
    public function save($spreadsheet, $file_path, $format = 'xlsx')
    {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, ucfirst($format));
        $writer->save($file_path);
        return true;
    }
} 