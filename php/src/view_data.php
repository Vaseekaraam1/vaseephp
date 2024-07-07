<?php
session_start();
include('db.php'); // Adjust the path to match your db.php location

// Check session
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}


// Function to fetch flower price details by ID
function fetchFlowerPriceDetails($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM flower_price WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

// Get the list of locations from the customers table for the dropdown
$locations_result = $conn->query("SELECT DISTINCT location FROM customer ORDER BY location");

// Get the selected location from the dropdown (if any)
$selected_location = isset($_POST['location']) ? $_POST['location'] : '';

// Fetch flower prices based on the selected location
if ($selected_location) {
    $stmt = $conn->prepare("SELECT fp.*, f.flower_name, f.luggage_cost 
                            FROM flower_price fp 
                            JOIN flower f ON fp.flower_id = f.id 
                            WHERE fp.location = ? 
                            ORDER BY fp.date_updated DESC");
    $stmt->bind_param("s", $selected_location);
    $stmt->execute();
    $prices_result = $stmt->get_result();
    $stmt->close();
} else {
    $prices_result = $conn->query("SELECT fp.*, f.flower_name, f.luggage_cost 
                                   FROM flower_price fp 
                                   JOIN flower f ON fp.flower_id = f.id 
                                   ORDER BY fp.date_updated DESC");
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Flower Prices</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
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

        h1 {
            margin: 0;
        }
        .control-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .control-row form {
            margin: 0;
        }
        label {
            font-weight: bold;
        }
        .location-select {
            width: 50%;
        }
        select {
            width: 100%;
            padding: 5px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #133b68;
            color: #fff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        header h1 {
            color: #2980b9;
        }
        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; /* 5% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Bloom Software</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="flowers.php">Flowers</a>
    <a href="billing.php">Billing</a>
    <a href="customer.php">Customers</a>
    <?php if ($_SESSION['role'] == 'superadmin') { ?>
        <a href="generate_invoice1.php">Generate Invoice</a>
        <a href="advance_payment.php">Advance Payment</a>
        <a href="lease_advances.php">Lease Advance</a>
        <a href="settings.php">Settings</a>
    <?php } ?>
   
    <a href="transaction_report.php">Transaction Report</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">
    <header>
        <h1>Flower Prices</h1>
    </header>
    <br><br>

    <div class="control-row">
        <form method="post" action="" class="location-select">
            <label for="location">Select Location:</label>
            <select name="location" id="location" onchange="this.form.submit()">
                <option value="">All Locations</option>
                <?php
                while ($location_row = $locations_result->fetch_assoc()) {
                    $location = $location_row['location'];
                    echo "<option value=\"$location\"".($location == $selected_location ? ' selected' : '').">$location</option>";
                }
                ?>
            </select>
        </form>
        <div class="button">
            <form action="flowers.php" method="get">
                <button type="submit">Back to Flowers</button>
            </form>
        </div>
    </div>

    <div class="flower-prices">
        <table>
            <thead>
                <tr>
                    <th>Flower Name</th>
                    <th>Shift 1 Price</th>
                    <th>Shift 2 Price</th>
                    <th>Shift 3 Price</th>
                    <th>Shift 4 Price</th>
                    <th>Date Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $prices_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['flower_name']; ?></td>
                        <td><?php echo $row['shift1_price']; ?></td>
                        <td><?php echo $row['shift2_price']; ?></td>
                        <td><?php echo $row['shift3_price']; ?></td>
                        <td><?php echo $row['shift4_price']; ?></td>
                        <td><?php echo date('Y-m-d', strtotime($row['date_updated'])); ?></td>
                        <td><a href="javascript:void(0);" onclick="openEditModal(<?php echo $row['id']; ?>)">Edit</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeEditModal()">&times;</span>
        <h2>Edit Flower Price</h2>
        <form id="editForm" action="update_flower_price.php" method="post">
            <input type="hidden" id="edit_id" name="id" value="">
            <label for="edit_shift1_price">Shift 1 Price:</label>
            <input type="text" id="edit_shift1_price" name="shift1_price" required><br><br>
            <label for="edit_shift2_price">Shift 2 Price:</label>
            <input type="text" id="edit_shift2_price" name="shift2_price" required><br><br>
            <label for="edit_shift3_price">Shift 3 Price:</label>
            <input type="text" id="edit_shift3_price" name="shift3_price" required><br><br>
            <label for="edit_shift4_price">Shift 4 Price:</label>
            <input type="text" id="edit_shift4_price" name="shift4_price" required><br><br>
            <button type="submit">Update</button>
        </form>
    </div>
</div>
<!-- End Edit Modal -->



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('#location').select2({
            placeholder: "Select a location",
            allowClear: true
        });
    });

    // Function to open edit modal and populate with data
    function openEditModal(id) {
        $.ajax({
            url: 'fetch_flower_price.php', // Replace with the actual PHP script to fetch flower price details
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                $('#edit_id').val(response.id);
                $('#edit_shift1_price').val(response.shift1_price);
                $('#edit_shift2_price').val(response.shift2_price);
                $('#edit_shift3_price').val(response.shift3_price);
                $('#edit_shift4_price').val(response.shift4_price);
                $('#editModal').css('display', 'block');
            },
            error: function(xhr, status, error) {
                alert('Error fetching data');
                console.error(xhr.responseText);
            }
        });
    }

    // Function to close edit modal
    function closeEditModal() {
        $('#editModal').css('display', 'none');
    }

    // Submit form handler for editing flower price
    $('#editForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            success: function(response) {
                alert('Price updated successfully');
                closeEditModal();
                // Optionally, update the table or reload the page to reflect changes
                location.reload();
            },
            error: function(xhr, status, error) {
                alert('Error updating price');
                console.error(xhr.responseText);
            }
        });
    });
</script>
