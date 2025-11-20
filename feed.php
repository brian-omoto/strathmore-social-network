<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Handle new post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];
    
    if (!empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$user_id, $content]);
        header('Location: feed.php?posted=1');
        exit();
    }
}

// Handle likes via GET (for simplicity)
if (isset($_GET['like_post'])) {
    $post_id = $_GET['like_post'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check if already liked
        $check_stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
        $check_stmt->execute([$user_id, $post_id]);
        
        if ($check_stmt->fetch()) {
            // Unlike
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
        } else {
            // Like
            $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $post_id]);
        }
        
        header('Location: feed.php');
        exit();
    } catch(PDOException $e) {
        // Like already exists, just redirect
        header('Location: feed.php');
        exit();
    }
}

// Get all posts with user info and like count
$stmt = $pdo->prepare("
    SELECT p.*, u.fullname, u.role, 
           COUNT(l.id) as like_count,
           EXISTS(SELECT 1 FROM likes WHERE user_id = ? AND post_id = p.id) as user_liked
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    LEFT JOIN likes l ON p.id = l.post_id 
    GROUP BY p.id 
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$posts = $stmt->fetchAll();

// Get comments for posts
$comments_stmt = $pdo->prepare("
    SELECT c.*, u.fullname 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    ORDER BY c.created_at ASC
");
$comments_stmt->execute();
$all_comments = $comments_stmt->fetchAll();

// Group comments by post_id
$comments_by_post = [];
foreach ($all_comments as $comment) {
    $comments_by_post[$comment['post_id']][] = $comment;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strathmore Connect - Feed</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js"></script>
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <h1>Strathmore Connect</h1>
            <div class="user-info">
                <span>Welcome, <?php echo $_SESSION['user_name']; ?>!</span>
                <a href="auth.php?logout=1" class="btn-logout">Logout</a>
            </div>
        </header>

        <!-- Create Post Section -->
        <div class="create-post">
            <h3>Create Post</h3>
            <form action="feed.php" method="POST">
                <textarea name="content" placeholder="What's on your mind? Share with the community..." rows="3" required></textarea>
                <div class="post-actions">
                    <button type="submit" class="btn-primary">Post</button>
                </div>
            </form>
            
            <?php if (isset($_GET['posted'])): ?>
                <div class="alert success" style="margin-top: 10px;">Post created successfully!</div>
            <?php endif; ?>
        </div>

        <!-- Feed -->
        <div class="feed">
            <h3>Community Feed</h3>
            
            <?php if (empty($posts)): ?>
                <div class="post">
                    <p>No posts yet. Be the first to share something!</p>
                </div>
            <?php else: ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post" data-post-id="<?php echo $post['id']; ?>">
                        <div class="post-header">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($post['fullname'], 0, 2)); ?>
                            </div>
                            <div class="user-info">
                                <strong><?php echo htmlspecialchars($post['fullname']); ?></strong>
                                <span><?php echo $post['role']; ?> • <?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="post-content">
                            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        </div>
                        
                        <div class="post-actions">
                            <button onclick="toggleLike(<?php echo $post['id']; ?>, this)" 
                                    class="like-btn <?php echo $post['user_liked'] ? 'liked' : ''; ?>">
                                👍 <?php echo $post['user_liked'] ? 'Liked' : 'Like'; ?> (<?php echo $post['like_count']; ?>)
                            </button>
                            <button onclick="toggleComments(<?php echo $post['id']; ?>)" class="comment-btn">
                                💬 Comment
                            </button>
                            <button class="share-btn">↗️ Share</button>
                        </div>

                        <!-- Comments Section -->
                        <div id="comments-<?php echo $post['id']; ?>" class="comments-section" style="display: none;">
                            <h4>Comments:</h4>
                            <div class="comments-list">
                                <?php if (isset($comments_by_post[$post['id']])): ?>
                                    <?php foreach ($comments_by_post[$post['id']] as $comment): ?>
                                        <div class="comment">
                                            <strong><?php echo htmlspecialchars($comment['fullname']); ?>:</strong>
                                            <span><?php echo htmlspecialchars($comment['content']); ?></span>
                                            <small><?php echo date('M j, g:i A', strtotime($comment['created_at'])); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>No comments yet. Be the first to comment!</p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Add Comment Form -->
                            <form onsubmit="event.preventDefault(); addComment(<?php echo $post['id']; ?>, this);" 
                                  class="add-comment">
                                <input type="text" name="comment_content" placeholder="Write a comment..." required>
                                <button type="submit" class="btn-primary">Post Comment</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <nav class="bottom-nav">
            <button class="nav-btn active">🏠 Feed</button>
            <button class="nav-btn" onclick="showGroups()">👥 Groups</button>
            <button class="nav-btn" onclick="showMessages()">💬 Messages</button>
            <button class="nav-btn" onclick="showProfile()">👤 Profile</button>
        </nav>
    </div>

    <script>
        // Like/Unlike functionality
        function toggleLike(postId, button) {
            // Simple implementation - redirect to like endpoint
            window.location.href = 'feed.php?like_post=' + postId;
        }

        // Add comment functionality
        function addComment(postId, form) {
            const content = form.querySelector('input[name="comment_content"]').value;
            
            if (!content.trim()) {
                alert('Please write a comment');
                return;
            }
            
            // Create form data
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('content', content);
            
            fetch('api/add_comment.php', {
                method: 'POST',
                body: formData
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
                    
                    // Remove "No comments" message if it exists
                    const noCommentsMsg = commentsContainer.querySelector('p');
                    if (noCommentsMsg && noCommentsMsg.textContent.includes('No comments')) {
                        noCommentsMsg.remove();
                    }
                    
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

        // Navigation functions
        function showGroups() { alert('Groups feature coming soon!'); }
        function showMessages() { alert('Messaging feature coming soon!'); }
        function showProfile() { alert('Profile page coming soon!'); }

        // Auto-refresh feed every 30 seconds
        setInterval(() => {
            // Simple refresh - reload the page
            // In a more advanced version, you'd use AJAX to update only new content
            // window.location.reload();
        }, 30000);
    </script>
</body>
</html>