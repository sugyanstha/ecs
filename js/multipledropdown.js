    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.dropdown-submenu a.dropdown-toggle').forEach(function(element) {
            element.addEventListener('click', function (e) {
                if (!this.nextElementSibling.classList.contains('show')) {
                    document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                        menu.classList.remove('show');
                    });
                }
                this.nextElementSibling.classList.toggle('show');
                e.stopPropagation();
            });
        });

        // Close submenus when clicking outside
        document.addEventListener('click', function () {
            document.querySelectorAll('.dropdown-submenu .dropdown-menu').forEach(function(menu) {
                menu.classList.remove('show');
            });
        });
    });
