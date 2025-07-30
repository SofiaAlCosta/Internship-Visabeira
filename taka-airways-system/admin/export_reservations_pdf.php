<?php
require_once '../libs/fpdf.php';
include '../includes/db.php';

$sql = "
    SELECT r.id, u.nome AS utilizador, v.origem, v.destino, v.data_partida, v.hora_partida
    FROM reservas r
    JOIN utilizadores u ON r.utilizador_id = u.id
    JOIN voos v ON r.voo_id = v.id
    ORDER BY r.data_reserva DESC
";

$resultado = $conn->query($sql);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Lista de Reservas', 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 8, 'ID', 1);
$pdf->Cell(40, 8, 'Utilizador', 1);
$pdf->Cell(30, 8, 'Origem', 1);
$pdf->Cell(30, 8, 'Destino', 1);
$pdf->Cell(25, 8, 'Data', 1);
$pdf->Cell(20, 8, 'Hora', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 10);
while ($linha = $resultado->fetch_assoc()) {
    $pdf->Cell(10, 8, $linha['id'], 1);
    $pdf->Cell(40, 8, utf8_decode($linha['utilizador']), 1);
    $pdf->Cell(30, 8, utf8_decode($linha['origem']), 1);
    $pdf->Cell(30, 8, utf8_decode($linha['destino']), 1);
    $pdf->Cell(25, 8, $linha['data_partida'], 1);
    $pdf->Cell(20, 8, $linha['hora_partida'], 1);
    $pdf->Ln();
}

$pdf->Output('D', 'reservations.pdf');
exit;