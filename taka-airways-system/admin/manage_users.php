<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT id, nome, email, tipo FROM utilizadores";
$resultado = $conn->query($sql);
?>

<?php include 'includes/header_admin.php'; ?>

<main>
    <h2>Gestão de Utilizadores</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Tipo</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td><?= htmlspecialchars($u['nome']) ?></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= $u['tipo'] ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $u['id'] ?>">Editar</a> |
                        <a href="delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Tens a certeza que queres eliminar este utilizador?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include '../includes/footer.php'; ?>