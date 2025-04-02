// Script untuk admin panel
document.addEventListener('DOMContentLoaded', function() {
    // Toggle mobile menu
    const menuBtn = document.getElementById('menu-btn');
    if(menuBtn) {
        menuBtn.onclick = () => {
            document.querySelector('.navbar').classList.toggle('active');
        };
    }
    
    // Notification button
    const notificationBtn = document.getElementById('notification-btn');
    if(notificationBtn) {
        notificationBtn.onclick = () => {
            alert('Fitur notifikasi akan datang!');
        };
    }
    
    // Profile button
    const profileBtn = document.getElementById('profile-btn');
    if(profileBtn) {
        profileBtn.onclick = () => {
            alert('Fitur profil akan datang!');
        };
    }
    
    // Logout confirmation
    const logoutBtn = document.getElementById('logout-btn');
    if(logoutBtn) {
        logoutBtn.onclick = (e) => {
            if(!confirm('Yakin ingin logout?')) {
                e.preventDefault();
            }
        };
    }
    
    // Status filter in orders page
    const statusFilter = document.querySelector('select[name="status"]');
    if(statusFilter) {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if(status) {
            statusFilter.value = status;
        }
    }
});