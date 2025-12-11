setInterval(function() {
    console.log('Dashboard auto-refresh check...');
}, 30000);

document.addEventListener('DOMContentLoaded', function() {
    const approveButtons = document.querySelectorAll('.btn-approve');
    const rejectButtons = document.querySelectorAll('.btn-reject');
    
    approveButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to approve this post?')) {
                e.preventDefault();
            }
        });
    });
    
    rejectButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to reject this post?')) {
                e.preventDefault();
            }
        });
    });
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.classList.add('loading');
        setTimeout(() => {
            card.classList.remove('loading');
        }, 500);
    });
});