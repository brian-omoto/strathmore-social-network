<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

if (isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Check if already liked
        $check_stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
        $check_stmt->execute([$user_id, $post_id]);
        
        if ($check_stmt->fetch()) {
            // Unlike - remove the like
            $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $post_id]);
            $action = 'unliked';
        } else {
            // Like - add the like
            $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $post_id]);
            $action = 'liked';
        }
        
        // Get updated like count
        $count_stmt = $pdo->prepare("SELECT COUNT(*) as like_count FROM likes WHERE post_id = ?");
        $count_stmt->execute([$post_id]);
        $like_count = $count_stmt->fetch()['like_count'];
        
        echo json_encode([
            'success' => true,
            'action' => $action,
            'like_count' => $like_count
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No post ID provided']);
}
?>