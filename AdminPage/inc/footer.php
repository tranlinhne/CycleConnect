<?php if (!isset($hideHeader)): ?>
        </div> 
    </div> 
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('menuToggle');
    const mainContent = document.getElementById('mainContent');
    const overlay = document.getElementById('sidebarOverlay');
    
    toggleBtn.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    });

    overlay.addEventListener('click', function() {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>
