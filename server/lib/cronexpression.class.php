<?php

/*
 * "mtdowling/cron-expression": "1.0.*"
 */

class CronExpression extends \Cron\CronExpression {

    private $once = FALSE;

    private $message_part = array(
        'once' => 'once',
        'every' => 'every',
        'and' => 'and',
        'minute' => 'minute',
        'hour' => 'hour',
        'day' => 'day',
        'weekday' => 'weekday',
        'month' => 'month',
        'year' => 'year',
        'first' => 'first',
        'last' => 'last',
        'next' => 'next',
        'previous' => 'previous',
        'of_week' => 'of week',
        'of_month' => 'of month',
        'of_year' => 'of year',
        'at' => 'at',
        'from' => 'from',
        'to' => 'to',
        '0w' => 'Sunday',
        '1w' => 'Monday',
        '2w' => 'Tuesday',
        '3w' => 'Wednesday',
        '4w' => 'Thursday',
        '5w' => 'Friday',
        '6w' => 'Saturday',
        '7w' => 'Sunday',
        '0m' => 'December_c',
        '1m' => 'January_c',
        '2m' => 'February_c',
        '3m' => 'March_c',
        '4m' => 'April_c',
        '5m' => 'May_c',
        '6m' => 'June_c',
        '7m' => 'July_c',
        '8m' => 'August_c',
        '9m' => 'September_c',
        '10m' => 'October_c',
        '11m' => 'November_c',
        '12m' => 'December_c'
    );

    private $timeParts = array(
        0 => 'minute',
        1 => 'hour',
        2 => 'day',
        3 => 'month',
        4 => 'weekday',
        5 => 'year',
    );

    private $message = array();

    private $currentTime = 'now';

    public function getMessageParts($part = FALSE) {
        if ($part !== FALSE) {
            return array_key_exists($part, $this->message_part) ? $this->message_part[$part] : 'undefined';
        }
        return $this->message_part;
    }

    public function setMessageParts($message_part, $str_val = '') {
        if (is_array($message_part)) {
            reset($message_part);
            while (list($key, $val) = each($message_part)) {
                $this->setMessageParts($key, $val);
            }
        } elseif (is_string($message_part) && !empty($str_val) && array_key_exists($message_part, $this->message_part)) {
            $this->message_part[$message_part] = $str_val;
        }
    }

    public function setCurrentTime($current_time = FALSE) {
        if ($current_time !== FALSE) {
            $this->currentTime = $current_time;
        }
    }

    public function __get($prop_name) {
        $class_vars = array();
        if ($class_vars = get_class_vars('CronExpression') && !array_key_exists($prop_name, $class_vars)) {
            $myClassReflection = new ReflectionClass('Cron\CronExpression');
            try {
                $secret = $myClassReflection->getProperty($prop_name);
                $secret->setAccessible(true);
                return $secret->getValue($this);
            } catch (ReflectionException $e) {}
        }
        throw new InvalidArgumentException($prop_name . ' is not a valid CRON property');
        return null;
    }

    public function getMessage() {
        if (empty($this->message)) {
            $this->setMessage();
        }
        return $this->message;
    }

    public function setMessage() {
        $this->setOnce();
        if ($this->once) {
            $this->message[$this->message_part['once']] = '';
        } else {
            foreach ($this->timeParts as $position => $name) {
                if (array_key_exists($position, $this->cronParts) && $this->fieldFactory->getField($position)) {
                    $part = $this->cronParts[$position];
                    $this->setPartMessage($position, $part, $name);
                }
            }

        }
    }

    protected function setOnce() {
        foreach ($this->cronParts as $key => $val) {
            $this->once &= is_numeric($val);
        }
    }

    protected function setPartMessage($position, $part, $name) {
        if (strpos($part, '/')) {
            list($part1, $part2) = explode('/', $part);
            $part1 = $part1 != '*' ? $this->setPartMessage($position, $part1, $name) : '';
            $part2 = $this->setPartMessage($position, "/$part2", $name);
            return $this->message[$position] = "$part1 $part2";
        }

        if (strpos($part, ',') !== FALSE && strpos($part, '|,|') === FALSE) {
            $coma_position = strrpos($part, ',');
            $part1 = substr($part, 0, $coma_position) . '|,|';
            $part2 = substr($part, $coma_position + 1);
            $part1 = $this->setPartMessage($position, $part1, $name);
            $part2 = $this->setPartMessage($position, $part2, $name);
            return $this->message[$position] = "$part1 {$this->message_part['and']} $part2";
        }

        $is_named_field = strpos($part, '/') !== FALSE;
        $part = str_replace('|,|', '', $part);
        if (($position == 3 || $position == 4) && !$is_named_field) {
            $message_part_name = '';
            $numbers = array();
            if (preg_match_all("/(\d+)/i", $part, $numbers) && !empty($numbers[1])) {
                rsort($numbers = $numbers[1]);
                foreach ($numbers as $key => $value) {
                    $part = str_replace($value, $this->message_part[$value . ($position != 3 ? 'w' : 'm')], $part);
                }
            }
        } else {
            $message_part_name = " {$this->message_part[$name]}";
        }
        if (strpos($part, '*') !== FALSE || strpos($part, '/') !== FALSE) {
            $message_part = str_replace(array( '/', '*' ), array( ' ', '' ), $part) . $message_part_name;
            return $this->message[$position] = !empty($message_part) ? $this->message_part['every'] . $message_part : '';
        } else {
            if (!$this->fieldFactory->getField($position)->isRange($part)) {
                $message_part = ' ' . $part . $message_part_name;
                return $this->message[$position] = trim($message_part) != '' ? $this->message_part['at'] . $message_part : '';
            } else {
                $message_part = ' ' . str_replace('-', " {$this->message_part['to']} ", $part) . $message_part_name;
                return $this->message[$position] = trim($message_part) != '' ? $this->message_part['from'] . $message_part : '';
            }
        }

        return $message_part;
    }

}