<?php
include 'connect.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$art_id = $data['art_id'];
$action = $data['action'];
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role == 'visitor') {
    $column = 'visitor_id';
} elseif ($user_role == 'artist') {
    $column = 'artist_id';
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid user role.']);
    exit;
}

$conn->begin_transaction();

try {
    if ($action === 'like') {
        $sql = "INSERT INTO likes ($column, art_id) VALUES (?, ?)";
        $update_likes_sql = "UPDATE art SET likes = likes + 1 WHERE id = ?";
    } else {
        $sql = "DELETE FROM likes WHERE $column = ? AND art_id = ?";
        $update_likes_sql = "UPDATE art SET likes = likes - 1 WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $art_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $update_likes_stmt = $conn->prepare($update_likes_sql);
        $update_likes_stmt->bind_param("i", $art_id);
        $update_likes_stmt->execute();

        $like_count_sql = "SELECT likes FROM art WHERE id = ?";
        $like_count_stmt = $conn->prepare($like_count_sql);
        $like_count_stmt->bind_param("i", $art_id);
        $like_count_stmt->execute();
        $like_count_result = $like_count_stmt->get_result();
        $like_count_row = $like_count_result->fetch_assoc();
        $like_count = $like_count_row['likes'];

        $conn->commit();
        echo json_encode(['success' => true, 'like_count' => $like_count]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'An error occurred.']);
    }

    $stmt->close();
    $update_likes_stmt->close();
    $like_count_stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
