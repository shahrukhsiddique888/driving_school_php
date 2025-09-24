<?php
class Schedule {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    public function all() {
        $sql = "SELECT s.id, st.name AS student, i.name AS instructor, v.make, v.model, 
                       s.start_time, s.end_time, s.status
                FROM schedule s
                JOIN students st ON s.student_id = st.id
                JOIN instructors i ON s.instructor_id = i.id
                LEFT JOIN vehicles v ON s.vehicle_id = v.id
                ORDER BY s.start_time DESC";
        return $this->db->query($sql)->fetchAll();
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO schedule 
            (student_id, instructor_id, vehicle_id, start_time, end_time, status) 
            VALUES (:student_id, :instructor_id, :vehicle_id, :start_time, :end_time, :status)");
        return $stmt->execute($data);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM schedule WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
