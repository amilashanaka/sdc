<?php

class TableController
{

    protected array $foreignKeys = [];

    protected PDO $conn;
    protected string $table;
    protected string $primaryKey;
    private ?int $id = null;

    public function __construct(Database $database, string $table, string $primaryKey = 'id', array $foreignKeys = [])
    {
        $this->conn = $database->get_connection();
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->foreignKeys = $foreignKeys;
    }

    public function set_id(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function get_id(): ?int
    {
        return $this->id;
    }

    public function set_foreign_keys(array $keys): self
    {
        $this->foreignKeys = array_unique(array_merge($this->foreignKeys, $keys));
        return $this;
    }

    public function get_connection(): PDO
    {
        return $this->conn;
    }

    public function set_connection($conn): self
    {
        $this->conn =  $conn;
        return $this;
    }

    public function get_table(): string
    {
        return $this->table;
    }

    public function set_table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function get_primary_key(): string
    {
        return $this->primaryKey;
    }

    public function set_primary_key(string $primaryKey): self
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    // Main Db Execution Method

    public function execute_query(string $query, array $params = [], string $queryType = 'SELECT'): array
    {
        try {
            $stmt = $this->conn->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
            }

            $stmt->execute();

            return match (strtoupper($queryType)) {
                'SELECTONE' => ['data' => $stmt->fetch(PDO::FETCH_ASSOC), 'error' => null],
                'SELECT' => ['data' => ($queryType === 'SELECT' ? $stmt->fetchAll(PDO::FETCH_ASSOC) : []), 'error' => null],
                'INSERT' => ['data' => ['last_insert_id' => $this->conn->lastInsertId()], 'error' => null, 'message' => "Record successfully inserted", 'code' => 200],
                'DELETE' => ['data' => ['affected_rows' => $stmt->rowCount()], 'error' => null, 'message' => "successfully Deleted", 'code' => 200],
                'UPDATE' => ['data' => ['affected_rows' => $stmt->rowCount()], 'error' => null, 'message' => "successfully updated", 'code' => 204],
                'SCALAR' => ['data' => ['value' => $stmt->fetchColumn()], 'error' => null],
                default => ['data' => [], 'error' => 'Invalid query type'],
            };
        } catch (PDOException $e) {

            return ['data' => [], 'error' => $e->getMessage(), 'code' => $e->getCode()];
        }
    }

    public function get_by_id(int $id, string $orderBy = ''): array
    {
        $query = "SELECT * FROM {$this->table} WHERE   {$this->primaryKey} = :id";
        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }

        $result = $this->execute_query($query, [':id' => $id], 'SELECTONE');

        // Handle case where no data is found
        if (!$result['data']) {
            $result['error'] = "No data found";
            $result['data'] = []; // Ensure data key is always present
        }

        return $result;
    }



    public function get_by_foreignKey(string $foreignKey, mixed $value, string $orderBy = ''): array
    {
        $query = "SELECT * FROM {$this->table} WHERE {$foreignKey} = :value";

        if ($orderBy) {
            $query .= " ORDER BY {$orderBy}";
        }

        $result = $this->execute_query($query, [':value' => $value], 'SELECT');

        // Ensure 'data' key is always present & handle no data found
        if (!$result['data']) {
            $result['error'] = "No data found";
            $result['data'] = [];
        }

        return $result;
    }



    public function get_all(string $filter = ''): array
    {
        return $this->execute_query("SELECT * FROM {$this->table} {$filter}", [], 'SELECT');
    }

    

    public function get_all_active(string $filter = ''): array
    {
        return $this->execute_query("SELECT * FROM {$this->table} WHERE status = 1 {$filter}", [], 'SELECT');
    }

    public function register(array $data, array $checkExisting = []): array
    {
        if (empty($data)) {
            return ['status' => 0, 'error' => 'Invalid input data'];
        }

        // Check if record exists based on $checkExisting fields
        if (!empty($checkExisting)) {
            $conditions = [];
            $params = [];
            foreach ($checkExisting as $field) {
                if (isset($data[$field])) {
                    $conditions[] = "{$field} = :{$field}";
                    $params[$field] = $data[$field];
                }
            }
            if (!empty($conditions)) {
                $query = "SELECT COUNT(*) FROM {$this->table} WHERE " . implode(' AND ', $conditions);
                $result = $this->execute_query($query, $params, 'SCALAR');
                if (!empty($result['data']['value']) && $result['data']['value'] > 0) {
                    return ['status' => 0, 'error' => 'Record already exists'];
                }
            }
        }

        $fields = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $query = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";

        return $this->execute_query($query, $data, 'INSERT');
    }

    public function update(array $data): array
    {
        if (empty($data['id'])) {
            return ['status' => 0, 'error' => 'Invalid ID'];
        }

        $id = $data['id'];
        unset($data['id']);

        $updateFields = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data)));
        $query = "UPDATE {$this->table} SET {$updateFields} WHERE {$this->primaryKey} = :id";

        return $this->execute_query($query, array_merge($data, ['id' => $id]), 'UPDATE');
    }

    public function updated_by_conditions(array $data, array $conditions): array
    {
        if (empty($data) || empty($conditions)) {
            return ['status' => 0, 'error' => 'No data or conditions provided'];
        }

        $updateFields = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data)));
        $whereClause = implode(' AND ', array_map(fn($key) => "{$key} = :cond_{$key}", array_keys($conditions)));

        $params = array_merge($data, array_combine(array_map(fn($key) => "cond_{$key}", array_keys($conditions)), $conditions));
        $query = "UPDATE {$this->table} SET {$updateFields} WHERE {$whereClause}";

        return $this->execute_query($query, $params, 'UPDATE');
    }

    public function delete(array $conditions): array
    {
        if (empty($conditions)) {
            return ['status' => 0, 'error' => 'No conditions provided'];
        }

        $whereClause = implode(' AND ', array_map(fn($key) => "{$key} = :{$key}", array_keys($conditions)));
        $query = "DELETE FROM {$this->table} WHERE {$whereClause}";

        return $this->execute_query($query, $conditions, 'DELETE');
    }

    public function delete_by_id(int $id): array
    {
        return $this->delete([$this->primaryKey => $id]);
    }
    public function get_count(): int
    {
        $result = $this->execute_query("SELECT COUNT({$this->primaryKey}) as total FROM {$this->table} WHERE status = 1", [], 'SCALAR');
        return isset($result['data']['value']) ? (int)$result['data']['value'] : 0;
    }

    public function get_all_by_category(string $category): array
    {
        return $this->execute_query("SELECT * FROM {$this->table} WHERE status = 1 AND category = :category", ['category' => $category], 'SELECT');
    }


    public function get_all_with_delete(): array
    {
        $result = array(
            'data' => [],
            'error' => null,
        );


        try {
            $query = "SELECT * FROM $this->table ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($data) {
                $result['data'] = $data;
                $result['error'] = null;
            } else {
                $result['error'] = "No data found";
            }
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
        }
        if ($result['error'] != 'No data found') {
            $_SESSION['error'] = $result['error'];
        }
        return $result;
    }


    public function get_all_paid(): array
    {
        $result = array(
            'data' => [],
            'error' => null,
        );

        try {
            $query = "SELECT * FROM $this->table WHERE f6 > 0.00";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($data) {
                $result['data'] = $data;
                $result['error'] = null;
            } else {
                $result['error'] = "No paid records found";
            }
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
        }
        if ($result['error'] != 'No paid records found') {
            $_SESSION['error'] = $result['error'];
        }
        return $result;
    }
    public function get_active(): array
    {
        
        return $this->execute_query("SELECT * FROM {$this->table} WHERE status = 1 ", [], 'SELECT');
    }


    
    public function get_data(array $params = [], string $orderBy = ''): array
    {
        // Initialize response structure
        $result = [
            'data' => [],
            'error' => null
        ];

        try {
            // Start query
            $query = "SELECT * FROM {$this->table} WHERE status = 1";

            // Construct the WHERE clause dynamically
            $conditions = [];
            foreach ($params as $field => $value) {
                $conditions[] = "$field = :$field";
            }

            // Append WHERE conditions if they exist
            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Append ORDER BY clause if provided
            if (!empty($orderBy)) {
                $query .= " ORDER BY {$orderBy}";
            }

            // Execute query using the optimized executeQuery method
            $result = $this->execute_query($query, $params, 'SELECT');
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
        }


        return $result;
    }

}
