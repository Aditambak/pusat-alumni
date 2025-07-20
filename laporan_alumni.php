<?php
session_start();
require_once 'config.php';
require_once 'fpdf.php'; // Memanggil pustaka FPDF



class PDF extends FPDF
{
    // --- FUNGSI BARU UNTUK MENGHITUNG JUMLAH BARIS ---
    var $widths;
    var $aligns;

    function SetWidths($w) { $this->widths=$w; }
    function SetAligns($a) { $this->aligns=$a; }

    function Row($data)
    {
        $nb=0;
        for($i=0;$i<count($data);$i++)
            $nb=max($nb,$this->NbLines($this->widths[$i],$data[$i]));
        $h=5*$nb;
        $this->CheckPageBreak($h);
        for($i=0;$i<count($data);$i++)
        {
            $w=$this->widths[$i];
            $a=isset($this->aligns[$i]) ? $this->aligns[$i] : 'L';
            $x=$this->GetX();
            $y=$this->GetY();
            $this->Rect($x,$y,$w,$h);
            $this->MultiCell($w,5,$data[$i],0,$a);
            $this->SetXY($x+$w,$y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h)
    {
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    function NbLines($w,$txt)
    {
        $cw=&$this->CurrentFont['cw'];
        if($w==0)
            $w=$this->w-$this->rMargin-$this->x;
        $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if($nb>0 and $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $nl=1;
        while($i<$nb)
        {
            $c=$s[$i];
            if($c=="\n")
            {
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
                continue;
            }
            if($c==' ')
                $sep=$i;
            $l+=$cw[$c];
            if($l>$wmax)
            {
                if($sep==-1)
                {
                    if($i==$j)
                        $i++;
                }
                else
                    $i=$sep+1;
                $sep=-1;
                $j=$i;
                $l=0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }
    // --- AKHIR FUNGSI BARU ---


    // Header Halaman
    function Header()
    {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,5,'Laporan Tracer Study',0,1,'C');
        $this->SetFont('Arial','',10);
        $this->Cell(0,7,'Universitas Ma Chung',0,1,'C');
        $this->Ln(5);
    }

    // Footer Halaman
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Halaman '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabel Data Alumni (Tetap sama)
    function AlumniTable($header, $data)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'1. Rekapitulasi Data Alumni',0,1,'L');
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(.3);
        $this->SetFont('','B');
        $w = array(25, 60, 20, 35, 50);
        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();
        $this->SetFont('','');
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0], 6, $row['nim'], 'LR', 0, 'L', $fill);
            $this->Cell($w[1], 6, $row['nama_lengkap'], 'LR', 0, 'L', $fill);
            $this->Cell($w[2], 6, $row['angkatan'], 'LR', 0, 'C', $fill);
            $this->Cell($w[3], 6, $row['status_pekerjaan'], 'LR', 0, 'L', $fill);
            $this->Cell($w[4], 6, $row['perusahaan_terkini'], 'LR', 0, 'L', $fill);
            $this->Ln();
            $fill = !$fill;
        }
        $this->Cell(array_sum($w), 0, '', 'T');
    }

    // --- FUNGSI TABEL FEEDBACK YANG DIPERBARUI ---
    function FeedbackTable($header, $data)
    {
        $this->SetFont('Arial','B',12);
        $this->Cell(0,10,'2. Rekapitulasi Feedback Akademik',0,1,'L');

        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('','B');
        
        // Header
        $w = array(35, 40, 15, 100); // Lebar kolom
        for($i=0; $i<count($header); $i++)
            $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', true);
        $this->Ln();

        // Data
        $this->SetFont('','');
        $this->SetWidths($w);
        foreach($data as $row)
        {
            $this->Row(array(
                $row['nama_lengkap'], 
                $row['mata_kuliah'], 
                $row['rating'].'/5', 
                $row['isi_feedback']
            ));
        }
    }
}

// --- Ambil Data (Tetap sama) ---
$sql_alumni = "SELECT nim, nama_lengkap, angkatan, status_pekerjaan, perusahaan_terkini FROM alumni WHERE status_verifikasi = 'terverifikasi' ORDER BY angkatan, nama_lengkap";
$result_alumni = $conn->query($sql_alumni);
$data_alumni = [];
if ($result_alumni->num_rows > 0) { while($row = $result_alumni->fetch_assoc()) { $data_alumni[] = $row; } }

$sql_feedback = "SELECT a.nama_lengkap, f.mata_kuliah, f.rating, f.isi_feedback FROM feedback f JOIN alumni a ON f.alumni_id = a.id ORDER BY f.tanggal_submit DESC";
$result_feedback = $conn->query($sql_feedback);
$data_feedback = [];
if ($result_feedback->num_rows > 0) { while($row = $result_feedback->fetch_assoc()) { $data_feedback[] = $row; } }

// --- Buat Dokumen PDF (Tetap sama) ---
$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

$header_alumni = array('NIM', 'Nama Lengkap', 'Angkatan', 'Status Kerja', 'Perusahaan Terkini');
$pdf->AlumniTable($header_alumni, $data_alumni);

$pdf->AddPage();

$header_feedback = array('Nama Alumni', 'Mata Kuliah', 'Rating', 'Isi Feedback');
$pdf->FeedbackTable($header_feedback, $data_feedback);

$pdf->Output('D', 'Laporan_Tracer_Study_Ma_Chung.pdf');

$conn->close();
?>
