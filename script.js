// script.js - Real-time AJAX functionality

// Add like functionality
function toggleLike(postId, button) {
    // Show loading state
    button.classList.add('loading');
    
    fetch('api/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update like count and button appearance
            button.innerHTML = `👍 ${data.action === 'liked' ? 'Liked' : 'Like'} (${data.like_count})`;
            
            // Toggle visual state
            if (data.action === 'liked') {
                button.classList.add('liked');
            } else {
                button.classList.remove('liked');
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    })
    .finally(() => {
        button.classList.remove('loading');
    });
}

// Add comment functionality
function addComment(postId, form) {
    const content = form.querySelector('input[name="comment_content"]').value;
    
    if (!content.trim()) {
        alert('Please write a comment');
        return;
    }
    
    fetch('api/add_comment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'post_id=' + postId + '&content=' + encodeURIComponent(content)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Add new comment to the list
            const commentsContainer = form.parentElement.querySelector('.comments-list');
            const newComment = document.createElement('div');
            newComment.className = 'comment';
            newComment.innerHTML = `
                <strong>${data.comment.fullname}:</strong>
                <span>${data.comment.content}</span>
                <small>${data.comment.created_at}</small>
            `;
            
            commentsContainer.appendChild(newComment);
            
            // Clear the input field
            form.querySelector('input[name="comment_content"]').value = '';
            
            // Show success message
            const successMsg = document.createElement('div');
            successMsg.className = 'alert success';
            successMsg.textContent = 'Comment added!';
            form.parentElement.insertBefore(successMsg, form);
            
            // Remove success message after 2 seconds
            setTimeout(() => {
                successMsg.remove();
            }, 2000);
            
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
}

// Toggle comments section
function toggleComments(postId) {
    const commentsSection = document.getElementById('comments-' + postId);
    if (commentsSection) {
        commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
    }
}

// Auto-refresh feed (basic real-time)
function startAutoRefresh() {
    // Refresh every 30 seconds to show new posts/comments
    setInterval(() => {
        // You can implement more sophisticated real-time updates here
        // For now, we'll just update like counts
        updateLikeCounts();
    }, 30000);
}

// Update like counts (simple implementation)
function updateLikeCounts() {
    document.querySelectorAll('.post').forEach(post => {
        const postId = post.dataset.postId;
        // In a more advanced version, you'd fetch updated counts from the server
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    startAutoRefresh();
});