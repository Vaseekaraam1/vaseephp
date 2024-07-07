$(document).ready(function() {
    // Show the modal for adding new advance payment
    $('#addNewAdvanceButton').click(function() {
        $('#addAdvanceModal').css('display', 'block');
    });

    // Close the modal when the close button is clicked
    $('.close').click(function() {
        $('#addAdvanceModal').css('display', 'none');
    });

    // Handle form submission to add new advance payment
    $('#addAdvanceForm').submit(function(event) {
        event.preventDefault();
        $.ajax({
            type: 'POST',
            url: 'advance_payment.php', // Ensure correct URL for processing form submission
            data: $(this).serialize() + '&add_advance_modal=1', // Add this parameter to distinguish form submission
            success: function(response) {
                alert(response); // Show success or error message
                location.reload(); // Refresh the page after submission
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Log error response
            }
        });
    });

    // Dynamically populate customers based on selected location in the modal
    $('#modal-location').change(function() {
        var location = $(this).val();
        $.ajax({
            type: 'POST',
            url: 'get_customers.php', // Replace with your script to fetch customers based on location
            data: { location: location },
            success: function(data) {
                $('#modal-customer').html(data);
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText); // Log error response
            }
        });
    });

    // Handle edit and delete buttons
    $('.edit-button').click(function() {
        var customerId = $(this).data('customer-id');
        var amount = $(this).data('amount');
        var date = $(this).data('date');

        // Pre-fill the form in the modal with existing data
        $('#modal-customer').val(customerId);
        $('#modal-amount').val(amount);
        $('#modal-date').val(date);

        // Show the modal for editing
        $('#addAdvanceModal').css('display', 'block');
    });

    $('.delete-button').click(function() {
        var customerId = $(this).data('customer-id');
        var confirmation = confirm("Are you sure you want to delete the advance payment details?");
        if (confirmation) {
            $.ajax({
                type: 'POST',
                url: 'advance_payment.php', // Ensure correct URL for processing delete action
                data: { action: 'delete', customer_id: customerId },
                success: function(response) {
                    alert(response); // Show success or error message
                    location.reload(); // Refresh the page after deletion
                },
                error: function(xhr, status, error) {
                    console.error(xhr.responseText); // Log error response
                }
            });
        }
    });
});
