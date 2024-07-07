<?php
session_start();
include('db.php');

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch existing customers from the database
$sql = "SELECT * FROM customer";
$result = $conn->query($sql);
$customers = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Fetch distinct locations from the database
$location_sql = "SELECT DISTINCT location FROM customer WHERE location IS NOT NULL";
$location_result = $conn->query($location_sql);
$locations = [];
if ($location_result->num_rows > 0) {
    while ($row = $location_result->fetch_assoc()) {
        $locations[] = $row['location'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customers Management</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
    

        .content {
            margin-left: 220px;
            padding: 20px;
        }
        header h1 {
            color: #2980b9;
        }
        .top-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        body {
    font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif; 
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 200px;
    height: 100%;
    background-color: #012652; /* Sidebar color */
    color: #fff;
    padding: 20px;
    box-sizing: border-box; /* Consistent padding behavior */
}

.sidebar h2 {
    text-align: center;
}

.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    margin: 10px 0;
    padding: 10px;
    background-color: #1f497d; /* Link background color */
}

.sidebar a:hover {
    background-color: #133b68; /* Link hover color */
    text-decoration: none;
}

.content {
    margin-left: 220px; /* Ensuring the content starts after the sidebar */
    padding: 20px;
    box-sizing: border-box;
    width: calc(100% - 220px); /* Making sure the content takes up the remaining width */
}

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th:first-child, td:first-child {
            width: 30%;
        }
        th:nth-child(2), td:nth-child(2) {
            width: 20%;
        }
        th:nth-child(3), td:nth-child(3) {
            width: 20%;
            text-align: center;
        }
        th:nth-child(4), td:nth-child(2) {
            width: 30%;
        }
        table {
            margin: 0 auto;
            width: calc(100% - 10cm);
        }
        th {
            background-color: #133b68;
            color: #fff;
        }
        .notification {
            color: white;
            padding: 10px;
            margin: 10px 0;
            display: none;
            border-radius: 4px;
        }
        .success {
            background-color: green;
        }
        .error {
            background-color: red;
        }
        
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 200px;
    height: 100%;
    background-color: #012652; /* Sidebar color */
    color: #fff;
    padding: 20px;
    box-sizing: border-box; /* Consistent padding behavior */
}

.sidebar h2 {
    text-align: center;
}

.sidebar a {
    display: block;
    color: #fff;
    text-decoration: none;
    margin: 10px 0;
    padding: 10px;
    background-color: #1f497d; /* Link background color */
}

.sidebar a:hover {
    background-color: #133b68; /* Link hover color */
    text-decoration: none;
}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<div class="sidebar">
        <h2>Bloom Software</h2>
        
        <a href="flowers.php">Flowers</a>
        <a href="billing.php">Billing</a>
        <a href="customer.php">Customers</a>
        <?php if ($_SESSION['role'] == 'superadmin') { ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="advance_payment.php">Advance Payment</a>
            <a href="lease_advances.php">Lease Advance</a>
            <a href="settings.php">Settings</a>
        <?php } ?>
        <a href="generate_invoice1.php">Generate Invoice</a>
        <a href="transaction_report.php">Transaction Report</a>
        <a href="logout.php">Logout</a>
    </div>
    <div class="content">
        <header>
            <h1>Customer Management</h1>
            <button onclick="showForm('add')">New Customer</button>
        </header>
        <div class="customer-management">
            <div class="top-options">
                <select id="customer-filter" class="select2" style="width: 200px;">
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo htmlspecialchars($customer['name']); ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="location-filter" class="select2" style="width: 200px;">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $location): ?>
                        <option value="<?php echo htmlspecialchars($location); ?>"><?php echo htmlspecialchars($location); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h3 id="form-title">Add New Customer</h3>
                    <form id="customerForm" method="POST" onsubmit="submitForm(event)">
                        <input type="hidden" name="customer_id" id="customer_id">
                        <input type="text" name="name" id="name" placeholder="Customer Name" required>
                        <input type="text" name="location" id="location" placeholder="Location">
                        <input type="number" step="0.01" name="commission_amount" id="commission_amount" placeholder="Commission Amount">
                        <button type="submit" id="form-submit-button">Save</button>
                    </form>
                </div>
            </div>
            <div id="notification" class="notification"></div>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Commission Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customer-table-body">
                    <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($customer['name']); ?></td>
                            <td><?php echo htmlspecialchars($customer['location']); ?></td>
                            <td><?php echo htmlspecialchars($customer['commission_amount']); ?></td>
                            <td>
                                <button onclick="editCustomer(<?php echo htmlspecialchars(json_encode($customer)); ?>)">Edit</button>
                                <button class="delete-button" data-customer-id="<?php echo $customer['customer_id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.select2').select2();

            $('#customer-filter, #location-filter').on('change', function() {
                filterCustomers();
            });

            $('.delete-button').on('click', function() {
                var customerId = $(this).data('customer-id');
                if (confirm('Are you sure you want to delete this customer?')) {
                    $.ajax({
                        url: 'delete_customer.php',
                        type: 'POST',
                        data: { customer_id: customerId },
                        success: function(response) {
                            var notification = $('#notification');
                            notification.html(response);
                            if (response.includes("successfully")) {
                                notification.addClass('success').removeClass('error');
                            } else {
                                notification.addClass('error').removeClass('success');
                            }
                            notification.show().delay(5000).fadeOut();
                        },
                        error: function(xhr, status, error) {
                            var notification = $('#notification');
                            notification.html('An error occurred: ' + error);
                            notification.addClass('error').removeClass('success');
                            notification.show().delay(5000).fadeOut();
                        }
                    });
                }
            });
        });

        function showForm(action) {
            document.getElementById('myModal').style.display = 'block';
            if (action === 'add') {
                document.getElementById('form-title').innerText = 'Add New Customer';
                document.getElementById('customerForm').action = 'add_customer.php';
                document.getElementById('customer_id').value = '';
                document.getElementById('name').value = '';
                document.getElementById('location').value = '';
                document.getElementById('commission_amount').value = '';
                document.getElementById('form-submit-button').innerText = 'Save';
            }
        }

        function editCustomer(customer) {
            showForm('edit');
            document.getElementById('form-title').innerText = 'Edit Customer';
            document.getElementById('customerForm').action = 'edit_customer.php';
            document.getElementById('customer_id').value = customer.customer_id;
            document.getElementById('name').value = customer.name;
            document.getElementById('location').value = customer.location;
            document.getElementById('commission_amount').value = customer.commission_amount;
            document.getElementById('form-submit-button').innerText = 'Update';
        }

        function closeModal() {
            document.getElementById('myModal').style.display = 'none';
        }

        function submitForm(event) {
            event.preventDefault();
            const form = document.getElementById('customerForm');
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                var notification = $('#notification');
                if (data.success) {
                    notification.html('Customer saved successfully.').addClass('success').removeClass('error');
                    location.reload();
                } else {
                    notification.html('Error: ' + data.message).addClass('error').removeClass('success');
                }
                notification.show().delay(5000).fadeOut();
            })
            .catch(error => console.error('Error:', error));
        }

        function filterCustomers() {
            const searchName = $('#customer-filter').val().toLowerCase();
            const locationFilter = $('#location-filter').val().toLowerCase();
            const tableBody = document.getElementById('customer-table-body');
            const rows = tableBody.getElementsByTagName('tr');

            for (let i = 0; i < rows.length; i++) {
                const nameCell = rows[i].getElementsByTagName('td')[0];
                const locationCell = rows[i].getElementsByTagName('td')[1];
                const name = nameCell.textContent.toLowerCase();
                const location = locationCell.textContent.toLowerCase();

                if ((name.startsWith(searchName) || searchName === '') && (location.includes(locationFilter) || locationFilter === '')) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
