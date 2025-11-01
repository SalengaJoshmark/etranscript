<?php
session_start();
include("../db_connect.php");

// ✅ Only allow admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$message = "";

// ------------------------
// DELETE REQUEST DATA
// ------------------------
if (isset($_POST['confirm_delete_requests']) && $_POST['confirm_delete_requests'] === 'yes') {
    try {
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
        mysqli_query($conn, "TRUNCATE TABLE faculty_messages");
        mysqli_query($conn, "TRUNCATE TABLE transaction_log");
        mysqli_query($conn, "TRUNCATE TABLE request");
        mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");

        $message = "✅ All system request-related records have been cleared successfully!";
    } catch (Exception $e) {
        $message = "❌ Error clearing tables: " . $e->getMessage();
    }
}

// ------------------------
// DELETE PDF FILES
// ------------------------
$pdfDir = __DIR__ . '/../uploads/generated_pdfs/';
$pdfFiles = array_diff(scandir($pdfDir), array('.', '..')); // list PDFs

if (isset($_POST['pdf_action_confirmed'])) {
    if ($_POST['pdf_action_confirmed'] === 'delete_one' && !empty($_POST['pdf_file'])) {
        $fileToDelete = basename($_POST['pdf_file']);
        $filePath = $pdfDir . $fileToDelete;
        if (file_exists($filePath)) {
            unlink($filePath);
            $message = "✅ PDF '$fileToDelete' has been deleted!";
        } else {
            $message = "❌ PDF file does not exist.";
        }
    }

    if ($_POST['pdf_action_confirmed'] === 'delete_all') {
        foreach ($pdfFiles as $file) {
            unlink($pdfDir . $file);
        }
        $message = "✅ All PDF files have been deleted!";
    }

    // refresh file list
    $pdfFiles = array_diff(scandir($pdfDir), array('.', '..'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset System Data | Admin</title>
<style>
body { font-family:"Poppins",sans-serif; background:linear-gradient(to right,#d6f0e8,#b7d6f2); margin:0; }
.container { width:50%; margin:80px auto; background:white; padding:40px; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align:center; }
h2 { color:#1e3a8a; margin-bottom:10px;}
button { background:#dc2626; color:white; border:none; padding:10px 20px; border-radius:6px; cursor:pointer; font-weight:500; margin-top:15px; }
button:hover { background:#b91c1c; }
.success { background:#d1fae5; color:#065f46; padding:12px; border-radius:6px; margin-top:15px; }
.warning { background:#fef3c7; color:#92400e; padding:12px; border-radius:6px; margin-bottom:20px; }
.back { display:inline-block; margin-top:20px; text-decoration:none; background:#1e40af; color:white; padding:8px 16px; border-radius:6px; }
select { padding:8px; border-radius:5px; margin-right:10px; }

/* Modal styles */
.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:white; padding:30px; border-radius:10px; text-align:center; max-width:400px; }
.modal button { margin:10px; }
</style>
</head>
<body>

<div class="container">
  <h2>⚠️ Reset All Request Data</h2>
  <p class="warning">
    This will permanently delete <b>all transcript requests, transaction logs, and faculty messages</b>.<br>
    The tables will be reset and auto-increment IDs will restart from 1.<br>
    This action <b>cannot be undone</b>.
  </p>

  <button onclick="showModal('requests')">🚨 Delete All Request Data</button>

  <h2>🗂 Manage Generated PDFs</h2>
  <?php if (!empty($pdfFiles)): ?>
  <select id="pdfSelect">
    <?php foreach ($pdfFiles as $pdf): ?>
      <option value="<?= htmlspecialchars($pdf) ?>"><?= htmlspecialchars($pdf) ?></option>
    <?php endforeach; ?>
  </select>
  <button onclick="showModal('deleteOne')">🗑 Delete Selected PDF</button>
  <br><br>
  <button onclick="showModal('deleteAll')">🚨 Delete All PDFs</button>
  <?php else: ?>
    <p>No PDF files found.</p>
  <?php endif; ?>

  <?php if (!empty($message)): ?>
    <div class="success"><?= htmlspecialchars($message); ?></div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="back">⬅️ Back to Dashboard</a>
</div>

<!-- Modal -->
<div class="modal" id="confirmModal">
  <div class="modal-content">
    <p id="modalText"></p>
    <form method="POST" id="modalForm">
      <input type="hidden" name="confirm_delete_requests" id="confirmDeleteRequests">
      <input type="hidden" name="pdf_file" id="modalPdfFile">
      <input type="hidden" name="pdf_action_confirmed" id="modalPdfAction">
      <button type="submit" id="yesBtn">Yes</button>
      <button type="button" onclick="closeModal()">Cancel</button>
    </form>
  </div>
</div>

<script>
function showModal(action) {
    const modal = document.getElementById('confirmModal');
    const modalText = document.getElementById('modalText');
    const confirmRequests = document.getElementById('confirmDeleteRequests');
    const modalPdfFile = document.getElementById('modalPdfFile');
    const modalPdfAction = document.getElementById('modalPdfAction');

    // Reset hidden fields
    confirmRequests.value = '';
    modalPdfFile.value = '';
    modalPdfAction.value = '';

    if(action === 'requests'){
        modalText.innerText = 'Are you sure you want to delete ALL request data? This cannot be undone!';
        confirmRequests.value = 'yes';
    } else if(action === 'deleteOne'){
        const selectedPdf = document.getElementById('pdfSelect').value;
        modalText.innerText = `Are you sure you want to delete the PDF "${selectedPdf}"?`;
        modalPdfFile.value = selectedPdf;
        modalPdfAction.value = 'delete_one';
    } else if(action === 'deleteAll'){
        modalText.innerText = 'Are you sure you want to delete ALL PDFs?';
        modalPdfAction.value = 'delete_all';
    }

    modal.style.display = 'flex';
}

function closeModal() {
    document.getElementById('confirmModal').style.display = 'none';
}
</script>

</body>
</html>
