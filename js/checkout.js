// Function to set product details in the modal before showing
function setProductDetails(product_id, name, price, stock) {
    document.getElementById('product_id').value = product_id;
    document.getElementById('order_name').value = name;
    document.getElementById('order_price').value = 'NRs. ' + price;
    document.getElementById('order_quantity').max = stock;
    document.getElementById('order_quantity').value = 1;

    // Initialize total price
    updateTotalPrice();
}

// Function to update the total price based on quantity
function updateTotalPrice() {
    const price = parseFloat(document.getElementById('order_price').value.replace('NRs. ', ''));
    const quantity = parseInt(document.getElementById('order_quantity').value);
    const totalPrice = price * quantity;

    // Display the total price
    document.getElementById('order_total_price').value = 'NRs. ' + totalPrice.toFixed(2);
}

// Toggle payment fields based on selected payment method
function togglePaymentFields() {
    const paymentMethod = document.getElementById('payment_method').value;
    const creditCardDetails = document.getElementById('credit_card_details');
    const mobilePaymentDetails = document.getElementById('mobile_payment_details');

    if (paymentMethod === 'credit_card') {
        creditCardDetails.style.display = 'block';
        mobilePaymentDetails.style.display = 'none';
    } else if (paymentMethod === 'mobile_payment') {
        creditCardDetails.style.display = 'none';
        mobilePaymentDetails.style.display = 'block';
    } else {
        creditCardDetails.style.display = 'none';
        mobilePaymentDetails.style.display = 'none';
    }
}

// Call the function once to ensure correct field visibility on page load
window.onload = togglePaymentFields;