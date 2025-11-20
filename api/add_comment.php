<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (isset($_POST['post_id']) && isset($_POST['content'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $content = trim($_POST['content']);
    
    if (!empty($content)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $post_id, $content]);
            
            // Get the new comment with user info
            $comment_stmt = $pdo->prepare("
                SELECT c.*, u.fullname 
                FROM comments c 
                JOIN users u ON c.user_id = u.id 
                WHERE c.id = LAST_INSERT_ID()
            ");
            $comment_stmt->execute();
            $new_comment = $comment_stmt->fetch();
            
            echo json_encode([
                'success' => true,
                'comment' => [
                    'id' => $new_comment['id'],
                    'fullname' => $new_comment['fullname'],
                    'content' => $new_comment['content'],
                    'created_at' => date('M j, g:i A', strtotime($new_comment['created_at']))
                ]
            ]);
            
        } catch(PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
}
?>