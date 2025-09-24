<?php
class Schedule {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Get all schedules (with student, instructor, vehicle names)
    public function all() {
        $sql = "SELECT s.id, s.start_time, s.end_time, s.status,
                       u1.name AS student_name,
                       u2.name AS instructor_name,
                       CONCAT(v.make, ' ', v.model) AS vehicle_name
                FROM schedule s
                JOIN students st ON s.student_id = st.id
                JOIN users u1 ON st.user_id = u1.id
                JOIN instructors i ON s.instructor_id = i.id
                JOIN users u2 ON i.user_id = u2.id
                LEFT JOIN vehicles v ON s.vehicle_id = v.id
                ORDER BY s.start_time ASC";

        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    // Create a new schedule entry
    public function create($student_id, $instructor_id, $vehicle_id, $start_time, $end_time) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO schedule (student_id, instructor_id, vehicle_id, start_time, end_time, status)
             VALUES (?, ?, ?, ?, ?, 'booked')"
        );
        return $stmt->execute([$student_id, $instructor_id, $vehicle_id, $start_time, $end_time]);
    }

    // Delete a schedule
    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM schedule WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
