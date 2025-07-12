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
    $stmt = $conn->prepare("SELECT * FROM tickets WHERE company_name = ? ORDER BY ticket_id ASC");
    $stmt->bind_param("s", $_SESSION['user']['company_name']);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Fetch company details (now also fetch ticket)
$companyName = $_SESSION['user']['company_name'];
$stmt = $conn->prepare("SELECT company_name, tin_no, ticket FROM users WHERE company_name = ? LIMIT 1");
$stmt->bind_param("s", $companyName);
$stmt->execute();
$companyDetails = $stmt->get_result()->fetch_assoc();
$ticketsLeft = isset($companyDetails['ticket']) ? (int)$companyDetails['ticket'] : null;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ticketing System - Home</title>
    <link rel="stylesheet" href="layout.css" />
    <link rel="stylesheet" href="home.css" />
    <?php if (isset($_SESSION['user']) && $_SESSION['user']['identifier'] === 'ADMIN'): ?>
        <link rel="stylesheet" href="table.css" />
    <?php else: ?>
        <link rel="stylesheet" href="table_user.css" />
    <?php endif; ?>
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
                <div class="hamburger" id="hamburgerMenu" onclick="toggleSidebar()">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="ticket-count">
                    <?php if ($_SESSION['user']['identifier'] === 'ADMIN'): ?>
                        -
                    <?php else: ?>
                        Remaining Tickets: <?= $ticketsLeft ?>
                    <?php endif; ?>
                </div>
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
                                    <th class="sort-header" onclick="sortTable(4)">Status</th>
                                    <th class="sort-header" onclick="sortTable(5)">Complexity</th>
                                </tr>
                                <tr class="search-row">
                                    <th><input type="text" onkeyup="searchTable(0)" placeholder="Search Ticket No."></th>
                                    <th><input type="text" onkeyup="searchTable(1)" placeholder="Search Date"></th>
                                    <th><input type="text" onkeyup="searchTable(2)" placeholder="Search Company"></th>
                                    <th><input type="text" onkeyup="searchTable(3)" placeholder="Search Description"></th>
                                    <th><input type="text" onkeyup="searchTable(4)" placeholder="Search Status"></th>
                                    <th><input type="text" onkeyup="searchTable(5)" placeholder="Search Complexity"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0):
                                    $counter = 1;
                                ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr data-ticket-id="<?= htmlspecialchars($row['ticket_id']) ?>">
                                            <td data-label="Ticket No.">
                                                <div class="ticket-number-cell">
                                                    <?php if ($_SESSION['user']['identifier'] === 'ADMIN'): ?>
                                                        <span><?= htmlspecialchars($row['ticket_id']) ?></span>
                                                        <button class="edit-btn" onclick="showEditModal(<?= htmlspecialchars($row['ticket_id']) ?>)">Edit</button>
                                                    <?php else: ?>
                                                        <?= $counter++ ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td data-label="Date"><?= htmlspecialchars($row['date_created']) ?></td>
                                            <td data-label="Company Name"><?= htmlspecialchars($row['company_name']) ?></td>
                                            <td data-label="Description" class="description-cell" title="Double-click to view full details">
                                                <?= htmlspecialchars($row['description']) ?>
                                            </td>
                                            <td data-label="Status"><?= htmlspecialchars($row['status']) ?></td>
                                            <td data-label="Complexity"><?= htmlspecialchars($row['complexity']) ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align:center;">No tickets found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Create Ticket Form - only for non-admins -->
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['identifier'] !== 'ADMIN'): ?>
                    <div class="create-ticket-section" id="createTicketSection">
                        <h3>Create New Ticket</h3>
                        <?php if ($ticketsLeft === 0): ?>
                            <div style="color: red; font-weight: bold; margin-bottom: 10px;">You have reached your ticket limit. Please renew to continue creating tickets.</div>
                        <?php endif; ?>
                        <form method="POST" action="ticket.php" enctype="multipart/form-data" <?php if ($ticketsLeft === 0) echo 'style="pointer-events:none;opacity:0.5;"'; ?>>
                            <div class="form-group">
                                <input type="text" name="company_name" value="<?= htmlspecialchars($companyDetails['company_name']) ?>" readonly />
                            </div>
                            <div class="form-group">
                                <input type="text" name="from_who" placeholder="Created By" required />
                            </div>
                            <div class="form-group">
                                <textarea name="description" placeholder="Description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="attachment">Attachment (image/pdf/docx, max 5MB):</label>
                                <input type="file" name="attachment" id="attachment" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip,.rar" />
                            </div>
                            <button type="submit" <?php if ($ticketsLeft === 0) echo 'disabled'; ?>>Create Ticket</button>
                        </form>
                    </div>
                    <!-- Floating button for mobile to open create ticket modal -->
                    <button id="openCreateTicketBtn" class="floating-create-btn" type="button" onclick="openCreateTicketModal()" style="display:none;">
                        +
                    </button>
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
                        <div class="ticket-detail">
                            <strong>Attachment</strong>
                            <span id="modalAttachmentHeader"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Status</strong>
                            <span id="modalStatus"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Complexity</strong>
                            <span id="modalComplexity"></span>
                        </div>
                        <div class="ticket-detail">
                            <strong>Admin Reply</strong>
                            <div id="modalReply"></div>
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
                    <div class="form-group">
                        <label for="editStatus">Status</label>
                        <select id="editStatus" name="status" required>
                            <option value="unsolved">Unsolved</option>
                            <option value="solved">Solved</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editComplexity">Complexity</label>
                        <select id="editComplexity" name="complexity">
                            <option value="">-- Select Complexity --</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editReply">Admin Reply</label>
                        <textarea id="editReply" name="reply" placeholder="Type your reply here..."></textarea>
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

                    // Show attachment if available
                    const modalAttachment = document.getElementById('modalAttachment');
                    const modalAttachmentHeader = document.getElementById('modalAttachmentHeader');
                    if (modalAttachment && modalAttachmentHeader) {
                        if (data.attachment) {
                            const ext = data.attachment.split('.').pop().toLowerCase();
                            if (["jpg", "jpeg", "png", "gif"].includes(ext)) {
                                modalAttachment.innerHTML = `<a href='${data.attachment}' target='_blank'><img src='${data.attachment}' alt='Attachment' style='max-width:100%;max-height:150px;display:block;margin-top:10px;'/></a>`;
                                modalAttachmentHeader.textContent = '';
                            } else {
                                modalAttachment.innerHTML = `<a href='${data.attachment}' target='_blank'>View Attachment</a>`;
                                modalAttachmentHeader.textContent = '';
                            }
                        } else {
                            modalAttachment.innerHTML = '';
                            modalAttachmentHeader.textContent = 'No attachment';
                        }
                    }

                    document.getElementById('modalStatus').textContent = data.status;
                    document.getElementById('modalComplexity').textContent = data.complexity || '';
                    const modalReply = document.getElementById('modalReply');
                    modalReply.innerHTML = data.reply ? data.reply : '';
                    // Dynamically inject admin reply form if admin and unsolved
                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['identifier'] === 'ADMIN'): ?>
                        if (data.status === 'unsolved') {
                            modalReply.innerHTML += `
                                <form id="adminReplyForm" style="margin-top:10px;" onsubmit="submitAdminReply(event)">
                                    <textarea id="adminReplyInput" name="reply" placeholder="Type your reply..." required style="width:100%;min-height:60px;"></textarea>
                                    <button type="submit" style="margin-top:5px;">Send Reply</button>
                                    <input type="hidden" id="adminReplyTicketId" name="ticket_id" value="${data.ticket_id}" />
                                </form>
                            `;
                            // Attach submit handler
                            setTimeout(() => {
                                const form = document.getElementById('adminReplyForm');
                                if (form) {
                                    form.onsubmit = submitAdminReply;
                                }
                            }, 0);
                        }
                    <?php endif; ?>

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
                    document.getElementById('editStatus').value = data.status;
                    document.getElementById('editReply').value = data.reply || '';
                    document.getElementById('editComplexity').value = data.complexity || '';

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

        // Admin reply submit handler
        function submitAdminReply(event) {
            event.preventDefault();
            const form = document.getElementById('adminReplyForm');
            const formData = new FormData(form);
            fetch('reply_ticket.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Refresh modal
                        showTicketDetails(formData.get('ticket_id'));
                    } else {
                        alert(data.message || 'Failed to reply.');
                    }
                })
                .catch(() => alert('Failed to reply.'));
        }
    </script>
    <script>
        // Hamburger menu toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('open');
        }

        // Show floating create ticket button on mobile
        function handleFloatingBtn() {
            const btn = document.getElementById('openCreateTicketBtn');
            const createSection = document.getElementById('createTicketSection');
            if (window.innerWidth <= 768) {
                if (btn) btn.style.display = 'block';
                if (createSection) createSection.classList.remove('show');
            } else {
                if (btn) btn.style.display = 'none';
                if (createSection) createSection.classList.add('show');
            }
        }
        window.addEventListener('resize', handleFloatingBtn);
        window.addEventListener('DOMContentLoaded', handleFloatingBtn);

        // Open create ticket modal on mobile
        function openCreateTicketModal() {
            const createSection = document.getElementById('createTicketSection');
            if (createSection) createSection.classList.add('show');
        }
        // Close create ticket modal when clicking outside (optional, can add close button if needed)
        document.addEventListener('click', function(e) {
            const createSection = document.getElementById('createTicketSection');
            const btn = document.getElementById('openCreateTicketBtn');
            if (window.innerWidth <= 768 && createSection && createSection.classList.contains('show')) {
                if (!createSection.contains(e.target) && e.target !== btn) {
                    createSection.classList.remove('show');
                }
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
