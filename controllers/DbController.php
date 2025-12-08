<?php

class DbController
{

    private $conn;


    public function __construct(Database $database)
    {
        $this->conn = $database->get_connection();
    }

    public function get_connection()
    {
        return $this->conn;
    }

    public function get_by_id($table,$id): array
    {
        $result = array(
            'data'=>[],
            'errors'=>null,
        );
    
        try {
            $query = "SELECT * FROM $table WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($data) {
                $result['data'] = $data;
                $result['error'] = null;
            } else {
                $result['error'] = "No data found";
            }
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
        }
       
        return $result;
    }
    

    public function get_all($table): array
    {
        $result = array(
            'data'=>[],
            'errors'=>null,
        );
    
        try {
            $query = "SELECT * FROM $table";
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
    
        return $result;
    }

    public function get_data($table, $params = array(), $orderBy = ''): array
    {

       
        $result = array(
            'data' => [],
            'errors' => null,
        );

        try {
            // Construct the query
            $query = "SELECT * FROM $table WHERE ";

            // Construct the WHERE clause from the params array
            $conditions = [];
            foreach ($params as $field => $value) {
                $conditions[] = "$field = :$field";
            }
            $query .= implode(" AND ", $conditions);

            // Append ORDER BY clause if $orderBy is provided
            if (!empty($orderBy)) {
                $query .= " ORDER BY $orderBy";
            }

            
            // Prepare the statement
            $stmt = $this->conn->prepare($query);

            // Bind parameters
            foreach ($params as $field => &$value) {
                $stmt->bindParam(":$field", $value);
            }

            // Execute the statement
            $stmt->execute();

            // Fetch the data
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process the result
            if ($data) {
                $result['data'] = $data;
                $result['error'] = null;
            } else {
                $result['error'] = "No data found";
            }
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    public function get_data_sql($query, $params = array()): array
    {
        $result = array(
            'data' => [],
            'errors' => null,
        );

        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

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

        return $result;
    }


    public function register($table,$data,$id_field='id'): array
    {
        $result = array();
        $fields = '';
        $placeholders = '';
        $params = array();



        foreach ($data as $field => $value) {
            $fields .= "$field, ";
            $placeholders .= ":$field, ";
            $params[$field] = $value;
        }

        $fields = rtrim($fields, ', ');
        $placeholders = rtrim($placeholders, ', ');

        try {

            $query = "INSERT INTO $table ($fields) VALUES ($placeholders)";
            $stmt = $this->conn->prepare($query);


            if ($stmt->execute($params)) {
                $result['status'] = $stmt->rowCount();
                $result['inserted_id'] = $this->conn->lastInsertId($id_field);
                $result['message'] = "Record successfully inserted";
                $result['code'] = 200;
                $result['error'] = null;
            } else {
                $result['error'] = "Insert failed";
                $result['code'] = 200;
            }
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
            $result['code'] = 400;
        }

        return $result;
    }

    public function update($table,$data): array
    {
        $result = array();
        $out = array();


        $id = $data['id'] ?? 0;

        if ($id > 0) {
            $result = $this->get_by_id($table,$id);

            if ($result['error'] == null) {
                $update_fields = '';
                $params = [];

                foreach ($data as $field => $value) {
                    if (array_key_exists($field, $result['data'])) {
                        if ($result['data'][$field] != $value) {
                            $update_fields .= "$field = :$field, ";
                            $params[$field] = $value;
                            $msg = "successfully updated";
                        } else {
                            $msg = "not change";
                        }
                        $out[$field] = $msg;
                    } else {
                        $out[$field] = "incorrect input";
                        $result['error'] = "incorrect input";
                    }
                }

                $update_fields = rtrim($update_fields, ', ');

                if (!empty($update_fields)) {
                    try {
                        $query = "UPDATE $table SET $update_fields WHERE id = :id";
                        $params['id'] = $id;

                        $stmt = $this->conn->prepare($query);

                        if ($stmt->execute($params)) {
                            $result['status'] = $stmt->rowCount();
                        } else {
                            $result['error'] = "Update failed";
                        }
                    } catch (PDOException $e) {
                        $result['error'] = $e->getMessage();
                    }
                }

                $result['message'] = $out;
            }
        }

        return $result;
    }


public function delete($table,$id): array
{
    $result = array();

    // Check if id is valid
    if ($id > 0) {
        try {
            // Prepare the SQL statement
            $query = "DELETE FROM $table WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            // Execute the statement
            if ($stmt->execute()) {
                $result['status'] = $stmt->rowCount();
                $result['message'] = "Record successfully deleted";
            } else {
                $result['error'] = "Delete failed";
            }
        } catch (PDOException $e) {
            $result['error'] = $e->getMessage();
        }
    } else {
        $result['error'] = "Invalid ID";
    }

    return $result;
}




}
