// Sidebar Toggle
const allSideMenu = document.querySelectorAll('.sidebar .side-menu li a');

allSideMenu.forEach(item => {
    const li = item.parentElement;
    item.addEventListener('click', function() {
        allSideMenu.forEach(i => {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

// Toggle Sidebar
const menuBar = document.querySelector('.content nav .bx.bx-menu');
const sidebar = document.querySelector('.sidebar');

if (menuBar) {
    menuBar.addEventListener('click', function() {
        sidebar.classList.toggle('close');
    });
}

// Search Form Toggle
const searchBtn = document.querySelector('.content nav form .form-input button');
const searchBtnIcon = document.querySelector('.content nav form .form-input button .bx');
const searchForm = document.querySelector('.content nav form');

if (searchBtn) {
    searchBtn.addEventListener('click', function(e) {
        if (window.innerWidth < 576) {
            e.preventDefault();
            searchForm.classList.toggle('show');
            if (searchForm.classList.contains('show')) {
                searchBtnIcon.classList.replace('bx-search', 'bx-x');
            } else {
                searchBtnIcon.classList.replace('bx-x', 'bx-search');
            }
        }
    });
}

// Theme Toggle
const toggler = document.getElementById('theme-toggle');

if (toggler) {
    toggler.addEventListener('change', function() {
        if (this.checked) {
            document.body.classList.add('dark');
        } else {
            document.body.classList.remove('dark');
        }
    });
}

// Window Resize
window.addEventListener('resize', function() {
    if (window.innerWidth < 768) {
        sidebar.classList.add('close');
    } else {
        sidebar.classList.remove('close');
    }
    if (window.innerWidth > 576) {
        searchBtnIcon.classList.replace('bx-x', 'bx-search');
        searchForm.classList.remove('show');
    }
});

// Logout Function
function logout() {
    window.location.href = '../assets/logout.php';
}
