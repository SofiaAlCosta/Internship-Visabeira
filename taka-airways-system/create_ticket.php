<?php
require_once 'libs/fpdf.php';
require_once 'libs/phpqrcode/qrlib.php';
include 'includes/auth.php';
include 'includes/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Reserva inválida.");
}

$reserva_id = intval($_GET['id']);
$utilizador_id = $_SESSION['utilizador_id'];

$stmt = $conn->prepare("
    SELECT r.id AS reserva_id, u.nome, u.email, v.origem, v.destino, v.data_partida, v.hora_partida, v.preco, r.data_reserva, v.id AS voo_id
    FROM reservas r
    JOIN utilizadores u ON r.utilizador_id = u.id
    JOIN voos v ON r.voo_id = v.id
    WHERE r.id = ? AND r.utilizador_id = ?
");
$stmt->bind_param("ii", $reserva_id, $utilizador_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    die("Reserva não encontrada.");
}

$reserva = $resultado->fetch_assoc();

$qrTexto = "Reserva #{$reserva['reserva_id']}\nNome: {$reserva['nome']}\nEmail: {$reserva['email']}\nVoo: FL-" . str_pad($reserva['voo_id'], 3, '0', STR_PAD_LEFT) . "\nOrigem: {$reserva['origem']}\nDestino: {$reserva['destino']}\nData: {$reserva['data_partida']}\nHora: {$reserva['hora_partida']}\nPreço: " . number_format($reserva['preco'], 2) . " €";

$tempQR = tempnam(sys_get_temp_dir(), 'qr') . '.png';
QRcode::png($qrTexto, $tempQR, QR_ECLEVEL_L, 4);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$pdf->Image('assets/images/planelogo.png', 10, 10, 40);
$pdf->Ln(35);

$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode("Bilhete de Voo - TAKA Airways"), 0, 1, 'C');
$pdf->Ln(10);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, utf8_decode("Nome: ") . utf8_decode($reserva['nome']), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Email: ") . $reserva['email'], 0, 1);
$pdf->Cell(0, 10, utf8_decode("Voo: FL-") . str_pad($reserva['voo_id'], 3, '0', STR_PAD_LEFT), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Origem: ") . utf8_decode($reserva['origem']), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Destino: ") . utf8_decode($reserva['destino']), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Data: ") . $reserva['data_partida'], 0, 1);
$pdf->Cell(0, 10, utf8_decode("Hora: ") . $reserva['hora_partida'], 0, 1);
$pdf->Cell(0, 10, utf8_decode("Preço: " . number_format($reserva['preco'], 2) . " EUR"), 0, 1);
$pdf->Cell(0, 10, utf8_decode("Nº da Reserva: #") . $reserva['reserva_id'], 0, 1);
$pdf->Ln(10);

$pdf->Image($tempQR, 160, 20, 35);

$pdf->Output('I', 'bilhete_reserva_' . $reserva['reserva_id'] . '.pdf');

unlink($tempQR);
?>