

<?php
class Course {
    private $db;
    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function all() {
        return $this->db->query("SELECT * FROM courses ORDER BY created_at DESC")->fetchAll();
    }

    public function find($id) {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO courses (title, description, duration, price) 
                                    VALUES (:title, :description, :duration, :price)");
        $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':duration' => $data['duration'],
            ':price' => $data['price']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE courses SET title = :title, description = :description, duration = :duration, price = :price WHERE id = :id");
        return $stmt->execute([
            ':title' => $data['title'],
            ':description' => $data['description'],
            ':duration' => $data['duration'],
            ':price' => $data['price'],
            ':id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM courses WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
