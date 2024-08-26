document.addEventListener('DOMContentLoaded', function() {
    const dropbtns = document.querySelectorAll('.nav-link.dropdown-toggle');
    
    // Handle click on each dropdown button
    dropbtns.forEach(dropbtn => {
        dropbtn.addEventListener('click', function(event) {
            event.preventDefault(); // Prevent default anchor behavior
            
            // Close all other dropdowns
            dropbtns.forEach(btn => {
                if (btn !== this) {
                    const otherDropdownMenu = btn.nextElementSibling;
                    if (otherDropdownMenu && otherDropdownMenu.classList.contains('show')) {
                        otherDropdownMenu.classList.remove('show');
                    }
                }
            });
            
            // Toggle the current dropdown menu
            const dropdownMenu = this.nextElementSibling;
            if (dropdownMenu) {
                dropdownMenu.classList.toggle('show');
            }
        });
    });

    // Close all dropdowns if the user clicks outside of any dropdown
    window.addEventListener('click', function(event) {
        if (!event.target.matches('.nav-link.dropdown-toggle')) {
            dropbtns.forEach(dropbtn => {
                const dropdownMenu = dropbtn.nextElementSibling;
                if (dropdownMenu && dropdownMenu.classList.contains('show')) {
                    dropdownMenu.classList.remove('show');
                }
            });
        }
    });
});
