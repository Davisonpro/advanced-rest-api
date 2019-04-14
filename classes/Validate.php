<?php
/**
 * @package    BestShop
 * @author     Davison Pro <davisonpro.coder@gmail.com>
 * @copyright  2018 BestShop
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */

namespace BestShop;

use BestShop\Tools;

/**
 * Class Validate
 *
 * @package BestShop
 */
class Validate
{
    /**
     * Check for a float number validity.
     *
     * @param float $float Float number to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isFloat($float)
    {
        return strval((float) $float) == strval($float);
    }

    public static function isUnsignedFloat($float)
    {
        return strval((float) $float) == strval($float) && $float >= 0;
    }

    /**
     * Check for a float number validity.
     *
     * @param float $float Float number to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isOptFloat($float)
    {
        return empty($float) || Validate::isFloat($float);
    }

    /**
     * Check for a carrier name validity.
     *
     * @param string $name Carrier name to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isCarrierName($name)
    {
        return empty($name) || preg_match(Tools::cleanNonUnicodeSupport('/^[^<>;=#{}]*$/u'), $name);
    }

    /**
     * Check for name validity.
     *
     * @param string $name Name to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isName($name)
    {
        return preg_match(Tools::cleanNonUnicodeSupport('/^[^0-9!<>,;?=+()@#"°{}_$%:¤|]*$/u'), stripslashes($name));
    }

    /**
     * Check for price validity.
     *
     * @param string $price Price to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isPrice($price)
    {
        return preg_match('/^[0-9]{1,10}(\.[0-9]{1,9})?$/', $price);
    }

    /**
     * Check for price validity (including negative price).
     *
     * @param string $price Price to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isNegativePrice($price)
    {
        return preg_match('/^[-]?[0-9]{1,10}(\.[0-9]{1,9})?$/', $price);
    }

    /**
     * Check for product or category name validity.
     *
     * @param string $name Product or category name to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isCatalogName($name)
    {
        return preg_match(Tools::cleanNonUnicodeSupport('/^[^<>;=#{}]*$/u'), $name);
    }

	/**
     * Check for standard name validity.
     *
     * @param string $name Name to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isGenericName($name)
    {
        return empty($name) || preg_match(Tools::cleanNonUnicodeSupport('/^[^<>={}]*$/u'), $name);
    }

    /**
     * Check for HTML field validity (no XSS please !).
     *
     * @param string $html HTML field to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isCleanHtml($html, $allow_iframe = false)
    {
        $events = 'onmousedown|onmousemove|onmmouseup|onmouseover|onmouseout|onload|onunload|onfocus|onblur|onchange';
        $events .= '|onsubmit|ondblclick|onclick|onkeydown|onkeyup|onkeypress|onmouseenter|onmouseleave|onerror|onselect|onreset|onabort|ondragdrop|onresize|onactivate|onafterprint|onmoveend';
        $events .= '|onafterupdate|onbeforeactivate|onbeforecopy|onbeforecut|onbeforedeactivate|onbeforeeditfocus|onbeforepaste|onbeforeprint|onbeforeunload|onbeforeupdate|onmove';
        $events .= '|onbounce|oncellchange|oncontextmenu|oncontrolselect|oncopy|oncut|ondataavailable|ondatasetchanged|ondatasetcomplete|ondeactivate|ondrag|ondragend|ondragenter|onmousewheel';
        $events .= '|ondragleave|ondragover|ondragstart|ondrop|onerrorupdate|onfilterchange|onfinish|onfocusin|onfocusout|onhashchange|onhelp|oninput|onlosecapture|onmessage|onmouseup|onmovestart';
        $events .= '|onoffline|ononline|onpaste|onpropertychange|onreadystatechange|onresizeend|onresizestart|onrowenter|onrowexit|onrowsdelete|onrowsinserted|onscroll|onsearch|onselectionchange';
        $events .= '|onselectstart|onstart|onstop';

        if (preg_match('/<[\s]*script/ims', $html) || preg_match('/(' . $events . ')[\s]*=/ims', $html) || preg_match('/.*script\:/ims', $html)) {
            return false;
        }

        if (!$allow_iframe && preg_match('/<[\s]*(i?frame|form|input|embed|object)/ims', $html)) {
            return false;
        }

        return true;
    }

    /**
     * Check for date format.
     *
     * @param string $date Date to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isDateFormat($date)
    {
        return (bool) preg_match('/^([0-9]{4})-((0?[0-9])|(1[0-2]))-((0?[0-9])|([1-2][0-9])|(3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date);
    }

    /**
     * Check for date validity.
     *
     * @param string $date Date to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isDate($date)
    {
        if (!preg_match('/^([0-9]{4})-((?:0?[0-9])|(?:1[0-2]))-((?:0?[0-9])|(?:[1-2][0-9])|(?:3[01]))( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/', $date, $matches)) {
            return false;
        }

        return checkdate((int) $matches[2], (int) $matches[3], (int) $matches[1]);
    }

    public static function isDateOrNull($date)
    {
        if (is_null($date) || $date === '0000-00-00 00:00:00' || $date === '0000-00-00') {
            return true;
        }

        return self::isDate($date);
    }

	/**
     * Check for boolean validity.
     *
     * @param bool $bool Boolean to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isBool($bool)
    {
        return $bool === null || is_bool($bool) || preg_match('/^(0|1)$/', $bool);
    }

    /**
     * Check for table or identifier validity
     * Mostly used in database for table names and id_table.
     *
     * @param string $table Table/identifier to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isTableOrIdentifier($table)
    {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $table);
    }

    /**
     * Check for an integer validity.
     *
     * @param int $value Integer to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isInt($value)
    {
        return (string) (int) $value === (string) $value || $value === false;
    }

    /**
     * Check for an integer validity (unsigned).
     *
     * @param int $value Integer to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isUnsignedInt($value)
    {
        return (string) (int) $value === (string) $value && $value < 4294967296 && $value >= 0;
    }

    /**
     * Check for an percentage validity (between 0 and 100).
     *
     * @param float $value Float to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isPercentage($value)
    {
        return Validate::isFloat($value) && $value >= 0 && $value <= 100;
    }

    /**
     * Check for an integer validity (unsigned)
     * Mostly used in database for auto-increment.
     *
     * @param int $id Integer to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isUnsignedId($id)
    {
        return Validate::isUnsignedInt($id); /* Because an id could be equal to zero when there is no association */
    }

    public static function isNullOrUnsignedId($id)
    {
        return $id === null || Validate::isUnsignedId($id);
    }

    /**
     * Check object validity.
     *
     * @param object $object Object to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isLoadedObject($object)
    {
        return is_object($object) && $object->id;
    }

    public static function isMySQLEngine($engine)
    {
        return in_array($engine, array('InnoDB', 'MyISAM'));
    }

    public static function isTablePrefix($data)
    {
        // Even if "-" is theorically allowed, it will be considered a syntax error if you do not add backquotes (`) around the table name
        return preg_match(Tools::cleanNonUnicodeSupport('/^[a-z0-9_]+$/ui'), $data);
    }

    /**
     * Check if $data is a string.
     *
     * @param string $data Data to validate
     *
     * @return bool Validity is ok or not
     */
    public static function isString($data)
    {
        return is_string($data);
    }
}

