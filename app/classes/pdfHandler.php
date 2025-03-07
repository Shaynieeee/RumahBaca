<?php
class PdfHandler {
    private $upload_path;
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->upload_path = dirname(dirname(__DIR__)) . '/image/buku/';
        
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0777, true);
        }
    }
    
    public function uploadPdf($file, $kode_buku) {
        // Validasi file
        if($file['error'] != 0) {
            throw new Exception("Error uploading file");
        }
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if($file_ext != "pdf") {
            throw new Exception("Only PDF files allowed");
        }
        
        // Generate nama file unik
        $new_filename = uniqid() . '_' . $kode_buku . '.pdf';
        $target_path = $this->upload_path . $new_filename;
        
        // Upload file
        if(!move_uploaded_file($file['tmp_name'], $target_path)) {
            throw new Exception("Failed to upload file");
        }
        
        return $new_filename;
    }
    
    public function countPages($filename) {
        $pdf_path = $this->upload_path . $filename;
        if(!file_exists($pdf_path)) {
            throw new Exception("PDF file not found");
        }
        
        $pdftext = file_get_contents($pdf_path);
        return preg_match_all("/\/Page\W/", $pdftext, $dummy);
    }
}
