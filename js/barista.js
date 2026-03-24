document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const orderId = this.dataset.orderId;
        const newStatus = this.value;
        
        if (confirm(`Update order #${orderId} status to "${newStatus}"?`)) {
            fetch('../api/barista_update_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    order_id: orderId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Order status updated successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.error || 'Failed to update status'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update order status');
                location.reload();
            });
        } else {
            location.reload();
        }
    });
});