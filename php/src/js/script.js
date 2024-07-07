document.addEventListener('DOMContentLoaded', function() {
    const locationDropdown = $('#location');
    const salesDataTbody = document.getElementById('sales-data');

    // Initialize Select2 on the dropdown
    locationDropdown.select2({
        placeholder: 'Select a location',
        allowClear: true
    });

    // Function to fetch sales data based on location and date
    function fetchSalesData(location, date) {
        fetch(`get_sales_data.php?location=${location}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                console.log('Sales data fetched:', data); // Debugging log
                salesDataTbody.innerHTML = ''; // Clear previous data
                data.forEach(item => {
                    const row = document.createElement('tr');
                    const flowerNameCell = document.createElement('td');
                    const quantitySoldCell = document.createElement('td');

                    flowerNameCell.textContent = item.flower_name;
                    quantitySoldCell.textContent = item.quantity_sold;

                    row.appendChild(flowerNameCell);
                    row.appendChild(quantitySoldCell);
                    salesDataTbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching sales data:', error);
                // Fallback to fetching billing data if sales data not available
                fetchBillingData(location, date);
            });
    }

    // Function to fetch billing data if sales data for today is not available
    function fetchBillingData(location, date) {
        fetch(`get_billing_data.php?location=${location}&date=${date}`)
            .then(response => response.json())
            .then(data => {
                console.log('Billing data fetched:', data); // Debugging log
                salesDataTbody.innerHTML = ''; // Clear previous data
                data.forEach(item => {
                    const row = document.createElement('tr');
                    const flowerNameCell = document.createElement('td');
                    const quantitySoldCell = document.createElement('td');

                    flowerNameCell.textContent = item.flower_name;
                    quantitySoldCell.textContent = item.quantity;

                    row.appendChild(flowerNameCell);
                    row.appendChild(quantitySoldCell);
                    salesDataTbody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching billing data:', error);
                // Handle error gracefully or display a message
            });
    }

    // Fetch locations to populate the dropdown
    fetch('get_locations.php')
        .then(response => response.json())
        .then(data => {
            console.log('Locations fetched:', data); // Debugging log
            data.forEach(location => {
                const option = new Option(location, location);
                locationDropdown.append(option);
            });
            locationDropdown.trigger('change');
        });

    // Handle change event on location dropdown
    locationDropdown.on('change', function() {
        const selectedLocation = this.value;
        const today = new Date().toISOString().slice(0, 10); // Get today's date in YYYY-MM-DD format
        console.log('Selected location:', selectedLocation); // Debugging log

        // Fetch sales data for today's date
        fetchSalesData(selectedLocation, today);
    });
});
