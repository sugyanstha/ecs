// JavaScript for filtering products
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const productGrid = document.getElementById('productGrid');
    const productCards = productGrid.getElementsByClassName('product-card');

    searchInput.addEventListener('input', function() {
        const query = searchInput.value.toLowerCase(); // Get search query and convert to lowercase
        Array.from(productCards).forEach(function(card) {
            const productName = card.querySelector('.card-title').textContent.toLowerCase(); // Get product name
            const productDescription = card.querySelector('.card-text').textContent.toLowerCase(); // Get product description

            // Check if either product name or description contains the search query
            if (productName.includes(query) || productDescription.includes(query)) {
                card.style.display = ''; // Show product card if it matches
            } else {
                card.style.display = 'none'; // Hide product card if it doesn't match
            }
        });
    });
});