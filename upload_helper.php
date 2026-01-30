<?php
/**
 * Secure File Upload Helper
 * ป้องกันการอัปโหลดไฟล์ที่เป็นอันตราย
 */
class SecureUpload {
    
    private static $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    private static $allowed_mime_types = [
        'image/jpeg',
        'image/png', 
        'image/gif'
    ];
    private static $max_size = 2097152; // 2MB in bytes
    
    /**
     * Upload image securely
     * @param array $file - $_FILES array element
     * @param string $upload_dir - Directory to upload to
     * @param string $prefix - Prefix for filename
     * @return array - ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public static function uploadImage($file, $upload_dir = 'uploads/', $prefix = 'img') {
        
        // Check if file was uploaded
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'ไม่มีไฟล์หรืออัปโหลดผิดพลาด', 'path' => null];
        }
        
        // Check file size
        if ($file['size'] > self::$max_size) {
            return ['success' => false, 'error' => 'ไฟล์ใหญ่เกิน 2MB', 'path' => null];
        }
        
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime_type, self::$allowed_mime_types)) {
            return ['success' => false, 'error' => 'ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ JPG, PNG, GIF)', 'path' => null];
        }
        
        // Validate file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, self::$allowed_extensions)) {
            return ['success' => false, 'error' => 'นามสกุลไฟล์ไม่ถูกต้อง', 'path' => null];
        }
        
        // Generate secure filename
        $new_name = $prefix . '_' . bin2hex(random_bytes(16)) . '.' . $ext;
        
        // Create directory if not exists
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Move uploaded file
        $destination = $upload_dir . $new_name;
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'path' => $destination, 'error' => null];
        } else {
            return ['success' => false, 'error' => 'ไม่สามารถย้ายไฟล์ได้', 'path' => null];
        }
    }
}
