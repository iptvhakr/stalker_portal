<?php
/**
 * Cache database result.
 *
 * @package stalker_portal
 * @author zhurbitsky@gmail.com
 */

class CacheResult extends DatabaseResult
{

    private $data;

    public function __construct($data, $sql, $link = null) {

        $this->data       = $data;
        $this->sql        = $sql;
        $this->total_rows = count($data);
    }

    public function __destruct() {

    }

    public function as_array($return = false, $field = null) {

        if (!$return) {

            return $this;
        }

        $array = array();

        if ($this->total_rows > 0) {

            reset($this->data);

            if ($field !== null) {
                foreach ($this->data as $row) {
                    $array[] = $row[$field];
                }
            } else {
                foreach ($this->data as $row) {
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

        if (!$this->offsetExist($offset)) {
            return false;
        }

        $this->current_row = $offset;

        return true;
    }

    public function current() {

        return $this->data[$this->current_row];
    }

    public function next(){

        if ($this->current_row < $this->total_rows) {
            if (isset($this->data[$this->current_row])) {
                $this->current_row++;

                return $this->data[$this->current_row];
            } else {
                return false;
            }
        } else {
            return false;
        }

    }

    public function counter() {
        return $this->total_rows;
    }
}
