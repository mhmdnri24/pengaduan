<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'third_party/tcpdf/tcpdf.php';

class Pdf extends TCPDF
{
    public function __construct($orientation = 'L', $unit = 'mm', $format = 'LEGAL', $unicode = true, $encoding = 'UTF-8', $diskcache = false)
    {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache);
        
        // Set default header data
        $this->SetHeaderData('', 0, 'SIBANG - Sistem Informasi Pelaporan', 'Laporan Realisasi');
        
        // Set header and footer fonts
        $this->setHeaderFont(Array('helvetica', '', 8));
        $this->setFooterFont(Array('helvetica', '', 7));
        
        // Set default monospaced font
        $this->SetDefaultMonospacedFont('courier');
        
        // Set margins
        $this->SetMargins(7, 7, 7);
        $this->SetHeaderMargin(5);
        $this->SetFooterMargin(5);
        
        // Set auto page breaks
        $this->SetAutoPageBreak(TRUE, 7);
        
        // Set image scale factor
        $this->setImageScale(1.25);
        
        // Disable header/footer
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        
        // Set cell padding
        $this->setCellPaddings(1, 1, 1, 1);
        
        // Set cell margins
        $this->setCellMargins(0, 0, 0, 0);
    }
}

// Definisikan konstanta yang digunakan di controller
if (!defined('PDF_PAGE_ORIENTATION')) {
    define('PDF_PAGE_ORIENTATION', 'L');
}
if (!defined('PDF_UNIT')) {
    define('PDF_UNIT', 'mm');
}
if (!defined('PDF_PAGE_FORMAT')) {
    define('PDF_PAGE_FORMAT', 'LEGAL');
}
if (!defined('PDF_MARGIN_LEFT')) {
    define('PDF_MARGIN_LEFT', 7);
}
if (!defined('PDF_MARGIN_TOP')) {
    define('PDF_MARGIN_TOP', 7);
}
if (!defined('PDF_MARGIN_RIGHT')) {
    define('PDF_MARGIN_RIGHT', 7);
}
if (!defined('PDF_MARGIN_BOTTOM')) {
    define('PDF_MARGIN_BOTTOM', 7);
}
if (!defined('PDF_MARGIN_HEADER')) {
    define('PDF_MARGIN_HEADER', 5);
}
if (!defined('PDF_MARGIN_FOOTER')) {
    define('PDF_MARGIN_FOOTER', 5);
}
if (!defined('PDF_CREATOR')) {
    define('PDF_CREATOR', 'SIBANG');
}
if (!defined('PDF_IMAGE_SCALE_RATIO')) {
    define('PDF_IMAGE_SCALE_RATIO', 1.25);
} 