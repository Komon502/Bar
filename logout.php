<?php
session_start(); // เรียกใช้งาน session ก่อนเพื่อที่จะสั่งทำลาย

// ล้างค่าตัวแปร session ทั้งหมด
$_SESSION = [];

// ทำลาย session ทิ้ง
session_destroy();

// ส่งผู้ใช้กลับไปที่หน้า Login (หรือจะเปลี่ยนเป็น index.php ก็ได้)
header("Location: login.php");
exit();
