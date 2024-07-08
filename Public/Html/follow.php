<?php
include 'connect.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$art_id = isset($data['followee_id']) ? intval($data['followee_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

if (!$art_id || !$action || !$user_id || !$user_role) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit;
}

$conn->begin_transaction();

try {
    if ($user_role == 'visitor') {
        $visitor_id = $user_id;
        $artist_id = NULL;
    } elseif ($user_role == 'artist') {
        $visitor_id = NULL;
        $artist_id = $user_id;
    } else {
        throw new Exception("Invalid user role.");
    }

    if ($action === 'follow') {
        $sql = "INSERT INTO follow (visitor_id, artist_id, followee_id) VALUES (?, ?, ?)";
        $update_followers_sql = "UPDATE artist SET followers = followers + 1 WHERE id = ?";
        $update_following_sql = "UPDATE artist SET following = following + 1 WHERE id = ?";
    } elseif ($action === 'unfollow') {
        $sql = "DELETE FROM follow WHERE (visitor_id = ? OR artist_id = ?) AND followee_id = ?";
        $update_followers_sql = "UPDATE artist SET followers = followers - 1 WHERE id = ?";
        $update_following_sql = "UPDATE artist SET following = following - 1 WHERE id = ?";
    } else {
        throw new Exception("Invalid action.");
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare statement failed: " . $conn->error);
    }
    $stmt->bind_param("iii", $visitor_id, $artist_id, $art_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $update_followers_stmt = $conn->prepare($update_followers_sql);
        if (!$update_followers_stmt) {
            throw new Exception("Prepare update followers statement failed: " . $conn->error);
        }
        $update_followers_stmt->bind_param("i", $art_id);
        $update_followers_stmt->execute();

        $update_following_stmt = $conn->prepare($update_following_sql);
        if (!$update_following_stmt) {
            throw new Exception("Prepare update following statement failed: " . $conn->error);
        }
        $update_following_stmt->bind_param("i", $user_id);
        $update_following_stmt->execute();

        $follow_count_sql = "SELECT followers FROM artist WHERE id = ?";
        $follow_count_stmt = $conn->prepare($follow_count_sql);
        if (!$follow_count_stmt) {
            throw new Exception("Prepare follow count statement failed: " . $conn->error);
        }
        $follow_count_stmt->bind_param("i", $art_id);
        $follow_count_stmt->execute();
        $follow_count_result = $follow_count_stmt->get_result();
        $follow_count_row = $follow_count_result->fetch_assoc();
        $follow_count = $follow_count_row['followers'];

        $conn->commit();
        echo json_encode(['success' => true, 'follow_count' => $follow_count]);
    } else {
        throw new Exception("No rows affected.");
    }

    $stmt->close();
    $update_followers_stmt->close();
    $update_following_stmt->close();
    $follow_count_stmt->close();
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>
