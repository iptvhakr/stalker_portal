<?php
/**
 * MySQL database result.
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class MysqlResult extends DatabaseResult
{

    public function __construct($result, $sql, $link) {

        if ($result instanceof mysqli_result) {

            $this->total_rows = mysqli_num_rows($result);

        } elseif (is_bool($result)) {

            if ($result == false) {

                throw new MysqlException('Error: mysqli_query ' . mysqli_error($link) . '; query :' . $sql);

            } else {

                $this->insert_id  = mysqli_insert_id($link);
                $this->total_rows = mysqli_affected_rows($link);
            }
        }

        $this->result = $result;

        $this->sql = $sql;
    }

    public function __destruct() {

        if ($this->result instanceof mysqli_result) {
            mysqli_free_result($this->result);
        }
    }

    public function as_array($return = false, $field = null) {

        if (!$return) {
            return $this;
        }

        $array = array();

        if ($this->total_rows > 0) {

            mysqli_data_seek($this->result, 0);

            if ($field !== null) {
                while ($row = mysqli_fetch_assoc($this->result)) {
                    $array[] = $row[$field];
                }
            } else {
                while ($row = mysqli_fetch_assoc($this->result)) {
                    $array[] = $row;
                }
            }

        }

        return $array;
    }

    public function all($field = null) {

        return $this->as_array(true, $field);
    }

    public function seek($offset) {

        if ($this->offsetExist($offset) && mysqli_data_seek($this->result, $offset)) {

            $this->current_row = $offset;

            return true;
        } else {
            return false;
        }
    }

    public function current() {

        if (!$this->seek($this->current_row)) {
            return null;
        }

        return mysqli_fetch_assoc($this->result);
    }

    public function next() {

        if ($this->current_row < $this->total_rows) {
            if (mysqli_data_seek($this->result, $this->current_row)) {
                $this->current_row++;

                return mysqli_fetch_assoc($this->result);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function counter() {

        return $this->first('count(*)');
    }
    
    public function total_rows() {
        return $this->total_rows;
    }
}
