<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

class Dompdf_lib
{
    protected $dompdf;
    
    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('chroot', [FCPATH]);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('defaultMediaType', 'print');
        $options->set('isFontSubsettingEnabled', true);
        
        $this->dompdf = new Dompdf($options);
    }
    
    public function loadHtml($html)
    {
        // Ensure proper encoding
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $this->dompdf->loadHtml($html);
    }
    
    public function setPaper($size = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->setPaper($size, $orientation);
    }
    
    public function render()
    {
        $this->dompdf->render();
    }
    
    public function output($options = [])
    {
        return $this->dompdf->output($options);
    }
    
    public function stream($filename, $options = [])
    {
        $this->dompdf->stream($filename, $options);
    }
    
    public function generate($html, $filename = 'document.pdf', $output = 'download')
    {
        // Load HTML with proper encoding
        $this->loadHtml($html);
        
        // Set paper format
        $this->setPaper('A4', 'portrait');
        
        // Render PDF
        $this->render();
        
        // Set proper headers
        header('Content-Type: application/pdf');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        // Output based on parameter
        switch($output) {
            case 'download':
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                echo $this->output();
                break;
            case 'inline':
            default:
                header('Content-Disposition: inline; filename="' . $filename . '"');
                echo $this->output();
                break;
        }
        exit;
    }
}
