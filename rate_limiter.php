<?php
/**
 * Rate Limiter
 * ป้องกัน Brute Force Attack
 */
class RateLimiter {
    
    private $pdo;
    private $max_attempts = 5;
    private $lockout_time = 900; // 15 minutes in seconds
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->createTableIfNotExists();
    }
    
    private function createTableIfNotExists() {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            username VARCHAR(50),
            attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(ip_address),
            INDEX(attempt_time)
        )";
        try {
            $this->pdo->exec($sql);
        } catch(PDOException $e) {
            error_log("Rate limiter table creation error: " . $e->getMessage());
        }
    }
    
    public function isBlocked($ip) {
        // Clean old attempts
        $this->cleanOldAttempts();
        
        // Count recent attempts
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$ip, $this->lockout_time]);
        $count = $stmt->fetch()['attempts'];
        
        return $count >= $this->max_attempts;
    }
    
    public function recordAttempt($ip, $username = null) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO login_attempts (ip_address, username) VALUES (?, ?)"
        );
        $stmt->execute([$ip, $username]);
    }
    
    public function clearAttempts($ip) {
        $stmt = $this->pdo->prepare(
            "DELETE FROM login_attempts WHERE ip_address = ?"
        );
        $stmt->execute([$ip]);
    }
    
    public function getRemainingTime($ip) {
        $stmt = $this->pdo->prepare(
            "SELECT MAX(attempt_time) as last_attempt FROM login_attempts 
             WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)"
        );
        $stmt->execute([$ip, $this->lockout_time]);
        $result = $stmt->fetch();
        
        if ($result && $result['last_attempt']) {
            $last_time = strtotime($result['last_attempt']);
            $unlock_time = $last_time + $this->lockout_time;
            return max(0, $unlock_time - time());
        }
        return 0;
    }
    
    private function cleanOldAttempts() {
        try {
            $this->pdo->exec(
                "DELETE FROM login_attempts WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 1 DAY)"
            );
        } catch(PDOException $e) {
            error_log("Clean old attempts error: " . $e->getMessage());
        }
    }
}
