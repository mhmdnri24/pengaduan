<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Excel Library - Wrapper for PhpSpreadsheet
 * Menggantikan PHPExcel dengan PhpSpreadsheet untuk kompatibilitas yang lebih baik
 */
class Excel
{
    private $spreadsheet;

    public function __construct()
    {
        // Load PhpSpreadsheet via Composer autoload
        require_once FCPATH . 'vendor/autoload.php';

        // Create new Spreadsheet object
        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    }

    /**
     * Get the underlying PhpSpreadsheet object
     */
    public function getSpreadsheet()
    {
        return $this->spreadsheet;
    }

    /**
     * Get active sheet
     */
    public function getActiveSheet()
    {
        return $this->spreadsheet->getActiveSheet();
    }

    /**
     * Get properties
     */
    public function getProperties()
    {
        return $this->spreadsheet->getProperties();
    }

    /**
     * Create writer
     */
    public function createWriter($format = 'Excel2007')
    {
        return \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->spreadsheet, $format);
    }

    /**
     * Load file
     */
    public function load($file_path)
    {
        return \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
    }

    /**
     * Save file
     */
    public function save($file_path)
    {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save($file_path);
    }

    // Backward compatibility methods
    public function setActiveSheetIndex($index = 0)
    {
        return $this->spreadsheet->setActiveSheetIndex($index);
    }

    public function getSheet($index = 0)
    {
        return $this->spreadsheet->getSheet($index);
    }

    public function createSheet($index = null)
    {
        return $this->spreadsheet->createSheet($index);
    }
}