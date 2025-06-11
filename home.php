<?php
session_start();
include 'db.php';
include 'functions.php';

checkLogin();

// Fetch tickets based on user role
if ($_SESSION['user']['identifier'] === 'ADMIN') {
    // Admin can see all tickets
    $result = $conn->query("SELECT * FROM tickets ORDER BY ticket_id DESC");

    // Fetch all companies for the dropdown
    $companyResult = $conn->query("SELECT DISTINCT company_name FROM users WHERE company_name != 'ADMIN' ORDER BY company_name");
} else {
    // Regular users can only see their company's tickets
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE company_name = ? ORDER BY ticket_id DESC");
    $stmt->bind_param("s", $_SESSION['user']['company_name']);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Fetch company details
$companyName = $_SESSION['user']['company_name'];
$stmt = $conn->prepare("SELECT company_name, tin_no FROM users WHERE company_name = ? LIMIT 1");
$stmt->bind_param("s", $companyName);
$stmt->execute();
$companyDetails = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ticketing System - Home</title>
    <link rel="stylesheet" href="layout.css" />
    <link rel="stylesheet" href="home.css" />
    <link rel="stylesheet" href="table.css" />
    <link rel="stylesheet" href="modals.css" />
    <link rel="stylesheet" href="settings.css" />
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <a href="home.php" id="home" class="sidebar-btn active">Home</a>
            <a href="package.php" id="package" class="sidebar-btn">Package</a>
            <a href="payment.php" id="payment" class="sidebar-btn">Payment</a>
            <a href="about.php" id="about" class="sidebar-btn">About us</a>
        </div>

        <!-- Main Content -->
        <div class="main">
            <div class="top-bar">
                <div class="ticket-count">Tic: <?= $result->num_rows ?></div>
                <div class="settings-icon" onclick="toggleSettings()">&#9881;</div>
                <div class="settings-dropdown" id="settingsDropdown">
                    <div class="company-info">
                        <h4>Company Information</h4>
                        <div class="info-row">
                            <span class="info-label">Company Name:</span>
                            <span class="info-value"><?= htmlspecialchars($companyDetails['company_name']) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">TIN Number:</span>
                            <span class="info-value"><?= htmlspecialchars($companyDetails['tin_no']) ?></span>
                        </div>
                    </div>
                    <button class="logout-btn" onclick="confirmLogout()">Logout</button>
                </div>
            </div>

            <div class="content-wrapper">
                <!-- Table Section -->
                <div class="table-section">
                    <div class="ticket-table-container">
                        <table class="ticket-table">
                            <thead>
                                <tr>
                                    <th class="sort-header" onclick="sortTable(0)">Ticket No.</th>
                                    <th class="sort-header" onclick="sortTable(1)">Date</th>
                                    <th class="sort-header" onclick="sortTable(2)">Company Name</th>
                                    <th class="sort-header" onclick="sortTable(3)" style="text-align: left;">Description</th>
                                </tr>
                                <tr class="search-row">
                                    <th><input type="text" onkeyup="searchTable(0)" placeholder="Search Ticket No."></th>
                                    <th><input type="text" onkeyup="searchTable(1)" placeholder="Search Date"></th>
                                    <th><input type="text" onkeyup="searchTable(2)" placeholder="Search Company"></th>
                                    <th><input type="text" onkeyup="searchTable(3)" placeholder="Search Description"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0):
                                    $counter = 1;
                                ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr data-ticket-id="<?= htmlspecialchars($row['ticket_id']) ?>">
                                            <td>
                                                <div class="ticket-number-cell">
                                                    <?php if ($_SESSION['user']['identifier'] === 'ADMIN'): ?>
                                                        <span><?= htmlspecialchars($row['ticket_id']) ?></span>
                                                        <button class="edit-btn" onclick="showEditModal(<?= htmlspecialchars($row['ticket_id']) ?>)">Edit</button>
                                                    <?php else: ?>
                                                        <?= $counter++ ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($row['date_created']) ?></td>
                                            <td><?= htmlspecialchars($row['company_name']) ?></td>
                                            <td class="description-cell" title="Double-click to view full details">
                                                <?= htmlspecialchars($row['description']) ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align:center;">No tickets found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Create Ticket Form - Only visible for admin -->
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['identifier'] === 'ADMIN'): ?>
                    <div class="create-ticket-section">
                        <h3>Create New Ticket</h3>
                        <form method="POST" action="ticket.php">
                            <div class="form-group">
                                <select name="company_name" required>
                                    <option value="">Select Company</option>
                                    <?php while ($company = $companyResult->fetch_assoc()): ?>
                                        <option value="<?= htmlspecialchars($company['company_name']) ?>">
                                            <?= htmlspecialchars($company['company_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="text" name="from_who" placeholder="Created By" required />
                            </div>
                            <div class="form-group">
                                <textarea name="description" placeholder="Description" required></textarea>
                            </div>
                            <button type="submit">Create Ticket</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Ticket Modal -->
            <div id="ticketModal" class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Ticket Information</h3>
                    <button class="close-button" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-content">
                    <div class="modal-inner-content">
                        <div class="ticket-detail">
                            <strong>Ticket Number</strong>
                            <span id="modalTicketId"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Date Created</strong>
                            <span id="modalDate"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Company Name</strong>
                            <span id="modalCompany"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Created By</strong>
                            <span id="modalFrom"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Description</strong>
                            <div id="modalDescription"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Modal -->
            <div id="editModal" class="edit-modal">
                <h3>Edit Ticket</h3>
                <form id="editForm" class="edit-form" onsubmit="saveTicketEdit(event)">
                    <input type="hidden" id="editTicketId" name="ticket_id">
                    <div class="form-group">
                        <label for="editCompany">Company Name</label>
                        <select id="editCompany" name="company_name" required>
                            <option value="">Select Company</option>
                            <?php
                            // Reset the company result pointer
                            if (isset($companyResult)) {
                                $companyResult->data_seek(0);
                                while ($company = $companyResult->fetch_assoc()):
                            ?>
                                    <option value="<?= htmlspecialchars($company['company_name']) ?>">
                                        <?= htmlspecialchars($company['company_name']) ?>
                                    </option>
                            <?php
                                endwhile;
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editCreatedBy">Created By</label>
                        <input type="text" id="editCreatedBy" name="from_who" required>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea id="editDescription" name="description" required></textarea>
                    </div>
                    <div class="edit-form-buttons">
                        <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                        <button type="submit" class="save-btn">Save Changes</button>
                    </div>
                </form>
            </div>

            <div id="modalBackdrop" class="modal-backdrop"></div>
        </div>
    </div>

    <!-- JS for search filter -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers to ticket rows
            document.querySelectorAll('.ticket-table tbody tr').forEach(row => {
                row.addEventListener('dblclick', function() {
                    const ticketId = this.getAttribute('data-ticket-id');
                    if (ticketId) {
                        showTicketDetails(ticketId);
                    }
                });
            });

            // Sort by ticket number on page load and set initial sort indicator
            const headers = document.querySelectorAll('th.sort-header');
            headers[0].classList.add('asc');
            sortTable(0);
        });

        function showTicketDetails(ticketId) {
            fetch(`ticket_info.php?id=${ticketId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    document.getElementById('modalTicketId').textContent =
                        '<?= $_SESSION['user']['identifier'] ?>' === 'ADMIN' ?
                        data.ticket_id :
                        document.querySelector(`tr[data-ticket-id="${data.ticket_id}"]`).cells[0].textContent;
                    document.getElementById('modalDate').textContent = data.date_created;
                    document.getElementById('modalCompany').textContent = data.company_name;
                    document.getElementById('modalFrom').textContent = data.from_who;
                    document.getElementById('modalDescription').textContent = data.description;

                    const modal = document.getElementById('ticketModal');
                    const backdrop = document.getElementById('modalBackdrop');

                    // Show backdrop first
                    backdrop.style.display = 'block';
                    setTimeout(() => backdrop.classList.add('show'), 10);

                    // Then show modal
                    modal.style.display = 'block';
                    setTimeout(() => modal.classList.add('show'), 10);

                    // Prevent body scrolling
                    document.body.classList.add('modal-open');
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading ticket details. Please try again.');
                });
        }

        function showEditModal(ticketId) {
            // Fetch ticket details
            fetch(`ticket_info.php?id=${ticketId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    document.getElementById('editTicketId').value = data.ticket_id;
                    document.getElementById('editCompany').value = data.company_name;
                    document.getElementById('editCreatedBy').value = data.from_who;
                    document.getElementById('editDescription').value = data.description;

                    const editModal = document.getElementById('editModal');
                    const backdrop = document.getElementById('modalBackdrop');

                    editModal.style.display = 'block';
                    backdrop.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading ticket details. Please try again.');
                });
        }

        function closeEditModal() {
            const editModal = document.getElementById('editModal');
            const backdrop = document.getElementById('modalBackdrop');

            editModal.style.display = 'none';
            // Only hide backdrop if ticket modal is also not visible
            if (document.getElementById('ticketModal').style.display !== 'block') {
                backdrop.style.display = 'none';
            }
        }

        function closeModal() {
            const modal = document.getElementById('ticketModal');
            const backdrop = document.getElementById('modalBackdrop');
            const editModal = document.getElementById('editModal');

            // Remove show classes first
            modal.classList.remove('show');
            backdrop.classList.remove('show');

            // Wait for animation to complete before hiding
            setTimeout(() => {
                modal.style.display = 'none';
                // Only hide backdrop if edit modal is also not visible
                if (editModal.style.display !== 'block') {
                    backdrop.style.display = 'none';
                }
                // Re-enable body scrolling
                document.body.classList.remove('modal-open');
            }, 300);
        }

        function saveTicketEdit(event) {
            event.preventDefault();
            const formData = new FormData(document.getElementById('editForm'));

            fetch('edit_ticket.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh the page to show updated data
                        window.location.reload();
                    } else {
                        alert('Error updating ticket: ' + (data.message || 'Please try again.'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating ticket. Please try again.');
                });
        }

        // Close modals when clicking outside
        document.getElementById('modalBackdrop').addEventListener('click', function() {
            closeModal();
            closeEditModal();
        });

        // Close modals when pressing ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeEditModal();
            }
        });

        function searchTable(columnIndex) {
            // Get all search inputs
            const searchInputs = document.querySelectorAll('.search-row input');
            const rows = document.querySelectorAll(".ticket-table tbody tr");

            rows.forEach(row => {
                let shouldShow = true;

                // Check each search input
                searchInputs.forEach((input, index) => {
                    const filter = input.value.toLowerCase();
                    if (filter) { // Only check if there's a value in the search input
                        const cell = row.getElementsByTagName("td")[index];
                        const txtValue = cell ? cell.textContent || cell.innerText : "";
                        if (!txtValue.toLowerCase().includes(filter)) {
                            shouldShow = false;
                        }
                    }
                });

                row.style.display = shouldShow ? "" : "none";
            });
        }

        // Initialize sorting variables
        let currentSortColumn = 0; // Default to ticket number column
        let isAscending = true;

        function sortTable(columnIndex) {
            const table = document.querySelector('.ticket-table');
            const headers = table.querySelectorAll('th.sort-header');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Update sort direction
            if (currentSortColumn === columnIndex) {
                isAscending = !isAscending;
            } else {
                currentSortColumn = columnIndex;
                isAscending = true;
            }

            // Reset all headers and update current header
            headers.forEach(header => {
                header.classList.remove('asc', 'desc');
            });
            headers[columnIndex].classList.add(isAscending ? 'asc' : 'desc');

            // Sort rows
            rows.sort((a, b) => {
                let aValue = a.cells[columnIndex].textContent.trim();
                let bValue = b.cells[columnIndex].textContent.trim();

                // For ticket number column, extract number from the cell
                if (columnIndex === 0) {
                    aValue = a.cells[columnIndex].querySelector('span') ?
                        a.cells[columnIndex].querySelector('span').textContent.trim() : aValue;
                    bValue = b.cells[columnIndex].querySelector('span') ?
                        b.cells[columnIndex].querySelector('span').textContent.trim() : bValue;
                    const aNum = parseInt(aValue.replace(/\D/g, ''));
                    const bNum = parseInt(bValue.replace(/\D/g, ''));
                    return isAscending ? aNum - bNum : bNum - aNum;
                }

                // Handle date sorting
                if (columnIndex === 1) {
                    const aDate = new Date(aValue);
                    const bDate = new Date(bValue);
                    return isAscending ? aDate - bDate : bDate - aDate;
                }

                // Default string comparison
                return isAscending ?
                    aValue.localeCompare(bValue) :
                    bValue.localeCompare(aValue);
            });

            // Reorder rows in the table
            rows.forEach(row => tbody.appendChild(row));
        }

        function toggleSettings() {
            const dropdown = document.getElementById('settingsDropdown');
            dropdown.classList.toggle('show');

            // Close dropdown when clicking outside
            document.addEventListener('click', function closeDropdown(e) {
                if (!e.target.matches('.settings-icon') && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                    document.removeEventListener('click', closeDropdown);
                }
            });
        }

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'logout.php';
            }
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>
