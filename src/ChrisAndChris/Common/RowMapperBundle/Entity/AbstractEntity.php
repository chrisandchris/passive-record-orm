<?php
namespace ChrisAndChris\Common\RowMapperBundle\Entity;

/**
 * @name AbstractEntity
 * @version    1.0.0
 * @since      v2.1.0
 * @package    RowMapperBundle
 * @author     ChrisAndChris
 * @link       https://github.com/chrisandchris
 */
abstract class AbstractEntity {

    /**
     * Parses and formats a date using the DateTime-format method
     *
     * @param \DateTime $date
     * @param string    $format
     * @return null|string
     */
    public function _formatDate($date, $format = 'Y-m-d') {
        $date = $this->_parseDate($date);

        return $date->format($format);
    }

    /**
     * Parse a date string (or DateTime object)
     *
     * @param $date
     * @return \DateTime|null
     */
    public function _parseDate($date) {
        if ($date == null) {
            return null;
        }
        if ($date instanceof \DateTime) {
            return $date;
        }

        return new \DateTime($date);
    }
}
