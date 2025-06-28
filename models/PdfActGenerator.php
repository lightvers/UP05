<?php
require_once __DIR__ . '/../vendor/autoload.php';

class PdfActGenerator extends TCPDF {
    protected $title;
    protected $organization;
    
    public function __construct($title, $organization = "КГАПОУ Пермский Авиационный техникум им. А.Д. Швецова") {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->title = $title;
        $this->organization = $organization;
        
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor($this->organization);
        $this->SetTitle($this->title);
        $this->SetFont('dejavusans', '', 10);
    }
    
    public function Header() {
        if (file_exists(__DIR__ . '/../../images/logo.png')) {
            $this->Image(__DIR__ . '/../../images/logo.png', 10, 10, 30);
        }
        $this->SetFont('dejavusans', 'B', 12);
        $this->Cell(0, 10, $this->organization, 0, 1, 'C');
        $this->Ln(5);
        $this->SetFont('dejavusans', 'B', 14);
        $this->Cell(0, 10, $this->title, 0, 1, 'C');
        $this->Ln(5);
    }
    
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('dejavusans', 'I', 8);
        $this->Cell(0, 10, 'Страница '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, 0, 'C');
    }
    
 public function generateEquipmentTemporaryAct($data) {
    $this->AddPage();
    $this->SetFont('dejavusans', '', 10);
    
    // Исправленная строка - убраны лишние параметры из date() и Cell()
    $transferDate = date('d.m.Y', strtotime($data['transfer_date'] ?? date('Y-m-d')));
    $this->Cell(0, 10, 'г. Пермь    ' . $transferDate, 0, 1);
    
    $this->Ln(10);
    
    $text = $this->organization . " в целях обеспечения необходимым оборудованием для исполнения должностных обязанностей передаёт сотруднику " . 
            $data['employee_name'] . ", а сотрудник принимает от учебного учреждения следующее оборудование:";
    $this->MultiCell(0, 10, $text, 0, 'L');
    $this->Ln(5);
    
    $this->SetFont('dejavusans', 'B', 10);
    $this->Cell(100, 10, 'Наименование оборудования', 1, 0, 'C');
    $this->Cell(50, 10, 'Инвентарный номер', 1, 0, 'C');
    $this->Cell(40, 10, 'Стоимость, руб.', 1, 1, 'C');
    
    $this->SetFont('dejavusans', '', 10);
    foreach($data['equipment'] as $item) {
        $this->Cell(100, 10, $item['name'], 1);
        $this->Cell(50, 10, $item['inventory_number'] ?? '-', 1, 0, 'C');
        
        $cost = is_numeric($item['cost'] ?? 0) ? (float)$item['cost'] : 0;
        $this->Cell(40, 10, number_format($cost, 2, ',', ' '), 1, 1, 'R');
    }
    
    $this->Ln(10);
    $returnDate = isset($data['return_date']) ? date('d.m.Y', strtotime($data['return_date'])) : '____.__.____';
    $this->MultiCell(0, 10, "По окончанию должностных работ $returnDate, работник обязуется вернуть полученное оборудование.", 0, 'L');
    $this->Ln(15);
    
    $this->Cell(100, 10, $data['employee_name'], 0, 0, 'L');
    $this->Cell(90, 10, '_________________', 0, 1, 'R');
    
    $this->Output('Акт приема-передачи оборудования на временное пользование.pdf', 'I');
}
    
    public function generateConsumablesAct($data) {
        $this->AddPage();
        $this->SetFont('dejavusans', '', 10);
        $this->Cell(0, 10, 'г. Пермь    ' . date('d.m.Y'), 0, 1);
        $this->Ln(10);
        
        $text = $this->organization . " в целях обеспечения необходимым оборудованием для исполнения должностных обязанностей передаёт сотруднику " . 
                $data['employee_name'] . ", а сотрудник принимает от учебного учреждения следующие расходные материалы:";
        $this->MultiCell(0, 10, $text, 0, 'L');
        $this->Ln(5);
        
        $this->SetFont('dejavusans', 'B', 10);
        $this->Cell(100, 10, 'Наименование', 1, 0, 'C');
        $this->Cell(30, 10, 'Количество', 1, 0, 'C');
        $this->Cell(60, 10, 'Стоимость, руб.', 1, 1, 'C');
        
        $this->SetFont('dejavusans', '', 10);
        foreach($data['consumables'] as $item) {
            $this->Cell(100, 10, $item['name'], 1);
            $this->Cell(30, 10, $item['quantity'] ?? '-', 1, 0, 'C');
            
            $cost = is_numeric($item['cost'] ?? 0) ? (float)$item['cost'] : 0;
            $this->Cell(60, 10, number_format($cost, 2, ',', ' '), 1, 1, 'R');
        }
        
        $this->Ln(15);
        $this->Cell(100, 10, $data['employee_name'], 0, 0, 'L');
        $this->Cell(90, 10, '_________________', 0, 1, 'R');
        
        $this->Output('Акт приема-передачи расходных материалов.pdf', 'I');
    }
    
    public function generateEquipmentAct($data) {
        $this->AddPage();
        $this->SetFont('dejavusans', '', 10);
        $this->Cell(0, 10, 'г. Пермь    ' . date('d.m.Y'), 0, 1);
        $this->Ln(10);
        
        $text = $this->organization . " в целях обеспечения необходимым оборудованием для исполнения должностных обязанностей передаёт сотруднику " . 
                $data['employee_name'] . ", а сотрудник принимает от учебного учреждения следующее оборудование:";
        $this->MultiCell(0, 10, $text, 0, 'L');
        $this->Ln(5);
        
        $this->SetFont('dejavusans', 'B', 10);
        $this->Cell(100, 10, 'Наименование оборудования', 1, 0, 'C');
        $this->Cell(50, 10, 'Инвентарный номер', 1, 0, 'C');
        $this->Cell(40, 10, 'Стоимость, руб.', 1, 1, 'C');
        
        $this->SetFont('dejavusans', '', 10);
        foreach($data['equipment'] as $item) {
            $this->Cell(100, 10, $item['name'], 1);
            $this->Cell(50, 10, $item['inventory_number'] ?? '-', 1, 0, 'C');
            
            $cost = is_numeric($item['cost'] ?? 0) ? (float)$item['cost'] : 0;
            $this->Cell(40, 10, number_format($cost, 2, ',', ' '), 1, 1, 'R');
        }
        
        $this->Ln(15);
        $this->Cell(100, 10, $data['employee_name'], 0, 0, 'L');
        $this->Cell(90, 10, '_________________', 0, 1, 'R');
        
        $this->Output('Акт приема-передачи оборудования.pdf', 'I');
    }
}