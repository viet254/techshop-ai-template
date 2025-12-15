<?php
// Admin footer include for ArchitectUI layout
?>
            </div> <!-- /.app-main__inner -->
        </div> <!-- /.app-main__outer -->
    </div> <!-- /.app-main -->
</div> <!-- /.app-container -->
<script src="/assets/js/notify.js"></script>
<script>
// Sidebar toggle with hamburger animation
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.hamburger.close-sidebar-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const isClosed = document.body.classList.toggle('closed-sidebar');
            btn.classList.toggle('is-active', isClosed);
        });
    });
});
</script>
</body>
</html>

