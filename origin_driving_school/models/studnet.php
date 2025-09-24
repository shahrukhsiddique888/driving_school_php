<?php
class Student {
  private PDO $db;
  public function __construct(PDO $db) { $this->db = $db; }

  public function all(string $q = ''): array {
    if ($q !== '') {
      $stmt = $this->db->prepare("SELECT * FROM students WHERE first_name LIKE :q OR last_name LIKE :q ORDER BY id DESC");
      $stmt->execute([':q' => "%$q%"]);
    } else {
      $stmt = $this->db->query("SELECT * FROM students ORDER BY id DESC");
    }
    return $stmt->fetchAll();
  }

  public function create(array $data): int {
    $sql = "INSERT INTO students (user_id, first_name, last_name, phone, license_status, notes)
            VALUES (:user_id,:first_name,:last_name,:phone,:license_status,:notes)";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([
      ':user_id' => $data['user_id'],
      ':first_name' => $data['first_name'],
      ':last_name' => $data['last_name'],
      ':phone' => $data['phone'] ?? null,
      ':license_status' => $data['license_status'] ?? 'none',
      ':notes' => $data['notes'] ?? null,
    ]);
    return (int)$this->db->lastInsertId();
  }
}


