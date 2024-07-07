$(document).ready(function() {
    $('.select2').select2();

    $('#customer').change(function() {
        var customerId = $(this).val();

        $('#billingRecords').fadeOut();

        $.ajax({
            url: 'process_fetch_locations.php',
            method: 'POST',
            dataType: 'json',
            data: { customer_id: customerId },
            success: function(response) {
                $('#location').empty().append($('<option>', {
                    value: '',
                    text: 'Select Location'
                }));
                $.each(response.locations, function(index, value) {
                    $('#location').append($('<option>', {
                        value: value,
                        text: value
                    }));
                });
                $('#commission').val(response.commission);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching locations:', error);
            }
        });
    });

    $('#location').change(function() {
        var location = $(this).val();

        $.ajax({
            url: 'process_fetch_flowers.php',
            method: 'POST',
            dataType: 'json',
            data: { location: location },
            success: function(response) {
                $('.flower').empty().append($('<option>', {
                    value: '',
                    text: 'Select Flower'
                }));
                $.each(response.flowers, function(index, value) {
                    $('.flower').append($('<option>', {
                        value: value.id,
                        text: value.flower_name
                    }));
                });
            },
            error: function(xhr, status, error) {
                console.error('Error fetching flowers:', error);
            }
        });
    });

    $(document).on('change', '.flower', function() {
        var flowerId = $(this).val();
        var customerId = $('#customer').val();
        var shift = $('#shift').val();
        var location = $('#location').val();
        var rateInput = $(this).closest('.form-row').find('.rate');

        $.ajax({
            url: 'process_billing.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'fetch_rate',
                customer_id: customerId,
                shift: shift,
                location: location,
                flower_id: flowerId
            },
            success: function(response) {
                if (response.success) {
                    rateInput.val(response.rate);
                } else {
                    alert('Error fetching rate: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching rate:', error);
            }
        });
    });

    $(document).on('input', '.quantity', function() {
        var quantity = $(this).val();
        var rate = $(this).closest('.form-row').find('.rate').val();
        var totalInput = $(this).closest('.form-row').find('.total');
        var total = quantity * rate;
        totalInput.val(total.toFixed(2));
    });

    $('#billingForm').submit(function(event) {
        event.preventDefault(); // Prevent form submission

        $.ajax({
            url: 'process_billing.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('.notification').removeClass('error-message').addClass('success-message').text(response).fadeIn();
                setTimeout(function() {
                    $('.notification').fadeOut();
                }, 3000);
                $('#billingForm')[0].reset(); // Reset form fields
            },
            error: function(xhr, status, error) {
                $('.notification').removeClass('success-message').addClass('error-message').text('Error processing billing records: ' + error).fadeIn();
                setTimeout(function() {
                    $('.notification').fadeOut();
                }, 3000);
            }
        });
    });
});

function addFlower() {
    var html = `
        <div class="form-row">
            <div class="form-group">
                <label for="flower">Select Flower:</label>
                <select class="flower highlighted-input" name="flower[]" required>
                    <option value="">Select Flower</option>
                </select>
            </div>
            <div class="form-group">
                <label for="rate">Rate:</label>
                <input type="text" class="rate readonly-input highlighted-input" name="rate[]" readonly>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" class="quantity highlighted-input" name="quantity[]" required>
            </div>
            <div class="form-group">
                <label for="total">Total:</label>
                <input type="text" class="total readonly-input highlighted-input" name="total[]" readonly>
            </div>
            <div class="remove-button" onclick="removeFlower(this)">&#10006;</div>
        </div>
    `;
    
    $('#flowersContainer').append(html);

    var location = $('#location').val();
    $.ajax({
        url: 'process_fetch_flowers.php',
        method: 'POST',
        dataType: 'json',
        data: { location: location },
        success: function(response) {
            $('#flowersContainer').find('.flower').each(function() {
                if ($(this).children('option').length === 1) {
                    $(this).empty();
                    $(this).append($('<option>', {
                        value: '',
                        text: 'Select Flower'
                    }));
                    $.each(response.flowers, function(index, value) {
                        $(this).append($('<option>', {
                            value: value.id,
                            text: value.flower_name
                        }));
                    }.bind(this));
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching flowers:', error);
        }
    });
}

function removeFlower(element) {
    $(element).closest('.form-row').remove();
}
