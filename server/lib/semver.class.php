<?php
/*
 * Port of the https://github.com/vierbergenlars/php-semver
 *
 * */

class SemVerExpression
{
    protected static $global_single_version = '(([0-9]+)(\\.([0-9]+)(\\.([0-9]+)(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)';
    protected static $global_single_xrange = '(([0-9]+|[xX*])(\\.([0-9]+|[xX*])(\\.([0-9]+|[xX*])(-([0-9]+))?(-?([a-zA-Z-+][a-zA-Z0-9\\.\\-:]*)?)?)?)?)';
    protected static $global_single_comparator = '([<>]=?)?\\s*';
    protected static $global_single_spermy = '(~?)>?\\s*';
    protected static $range_mask = '%1$s\\s+-\\s+%1$s';
    protected static $regexp_mask = '/%s/';
    protected static $dirty_regexp_mask = '/^[v= ]*%s$/';
    private $chunks = array();

    /**
     * standardizes the comparator/range/whatever-string to chunks
     * @param string $versions
     */
    public function __construct($versions)
    {
        $versions = preg_replace(sprintf(self::$dirty_regexp_mask, self::$global_single_comparator . '(\\s+-\\s+)?' . self::$global_single_xrange), '$1$2$3', $versions); //Paste comparator and version together
        //Condense multiple spaces to one
        $versions = preg_replace('/\\s+/', ' ', $versions);
        // All the same wildcards, plz
        $versions = str_replace(array('*', 'X'), 'x', $versions);
        if (strstr($versions, ' - ')) {
            //Replace all ranges with comparators
            $versions = self::rangesToComparators($versions);
        }
        if (strstr($versions, '~')) {
            //Replace all spermies with comparators
            $versions = self::spermiesToComparators($versions);
        }
        if (strstr($versions, 'x') && (strstr($versions, '<')|| strstr($versions, '>'))) {
            // x-ranges and comparators in the same string
            $versions = self::compAndxRangesToComparators($versions);
        }
        if (strstr($versions, 'x')) {
            //Replace all x-ranges with comparators
            $versions = self::xRangesToComparators($versions);
        }
        $or = explode('||', $versions);
        foreach ($or as &$orchunk) {
            $and = explode(' ', trim($orchunk));
            foreach ($and as $order => &$achunk) {
                $achunk = self::standardizeSingleComparator($achunk);
                if (strstr($achunk, ' ')) {
                    $pieces = explode(' ', $achunk);
                    unset($and[$order]);
                    $and = array_merge($and, $pieces);
                }
            }
            $orchunk = $and;
        }
        $this->chunks = $or;
    }

    /**
     * Checks ifthis range is satisfied by the given version
     * @param  SemVer $version
     * @return boolean
     */
    public function satisfiedBy(SemVer $version)
    {
        $version1 = $version->getVersion();
        $expression = sprintf(self::$regexp_mask, self::$global_single_comparator . self::$global_single_version);
        $ok = false;
        foreach ($this->chunks as $orblocks) { //Or loop
            foreach ($orblocks as $ablocks) { //And loop
                $matches = array();
                preg_match($expression, $ablocks, $matches);
                $comparators = $matches[1];
                $version2 = $matches[2];
                if ($comparators === '') {
                    $comparators = '=='; //Use equal if no comparator is set
                }
                //If one chunk of the and-loop does not match...
                if (!SemVer::cmp($version1, $comparators, $version2)) {
                    $ok = false; //It is not okay
                    break; //And this loop will surely fail: return to or-loop
                } else {
                    $ok = true;
                }
            }
            if ($ok) {
                return true; //Only one or block has to match
            }
        }

        return false; //No matches found :(
    }

    /**
     * Get the whole or object as a string
     * @return string
     */
    public function getString()
    {
        $or = $this->chunks;
        foreach ($or as &$orchunk) {
            $orchunk = implode(' ', $orchunk);
        }

        return implode('||', $or);
    }

    /**
     * Get the object as an expression
     * @return string
     */
    public function __toString()
    {
        return $this->getString();
    }

    /**
     * Get the object as a range expression
     * @return string
     */
    public function validRange()
    {
        return $this->getString();
    }

    /**
     * Find the maximum satisfying version
     * @param  array|string                        $versions An array of version objects or version strings, one version string
     * @return SemVer|null
     */
    public function maxSatisfying($versions)
    {
        if (!is_array($versions)) {
            $versions = array($versions);
        }
        usort($versions, __NAMESPACE__ . '\\version::rcompare');
        foreach ($versions as $version) {
            try {
                if (!is_a($version, 'SemVer')) {
                    $version = new SemVer($version);
                }
            } catch (SemVerException $e) {
                // Invalid versions do never match
                continue;
            }
            if ($version->satisfies($this)) {
                return $version;
            }
        }

        return null;
    }

    /**
     * standardizes a single version
     * @param  string          $version
     * @param  bool            $padZero Set to true ifthe version string should be padded with zeros instead of x-es
     * @throws SemVerException
     * @return string
     */
    public static function standardize($version, $padZero = false)
    {
        $expression = sprintf(self::$dirty_regexp_mask, self::$global_single_version);
        if (!preg_match($expression, $version, $matches)) {
            throw new SemVerException('Invalid version string given', $version);
        }
        if ($padZero) { //If there is a comparator drop undefined parts
            self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, null);
            if ($build === '') {
                $build = null;
            }
            if ($prtag === '') {
                $prtag = null;
            }

            return self::constructVersionFromParts(false, $major, $minor, $patch, $build, $prtag);
        } else { //If it is just a number, convert to a range
            self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x');
            if ($build === '') {
                $build = null;
            }
            if ($prtag === '') {
                $prtag = null;
            }
            $version = self::constructVersionFromParts(false, $major, $minor, $patch, $build, $prtag);

            return self::xRangesToComparators($version);
        }
    }

    /**
     * standardizes a single version (typeo'd version for BC)
     * @deprecated 2.1.0
     * @param  string          $version
     * @param  bool            $padZero Set to true ifthe version string should be padded with zeros instead of x-es
     * @throws SemVerException
     * @return string
     */
    public static function standarize($version, $padZero = false)
    {
        return self::standardize($version, $padZero);
    }

    /**
     * standardizes a single version with comparators
     * @param  string          $version
     * @throws SemVerException
     * @return string
     */
    protected static function standardizeSingleComparator($version)
    {
        $expression = sprintf(self::$regexp_mask, self::$global_single_comparator . self::$global_single_version);
        if (!preg_match($expression, $version, $matches)) {
            throw new SemVerException('Invalid version string given', $version);
        }
        $comparators = $matches[1];
        $version = $matches[2];
        $hasComparators = true;
        if ($comparators === '') {
            $hasComparators = false;
        }
        $version = self::standardize($version, $hasComparators);

        return $comparators . $version;
    }

    /**
     * standardizes a bunch of versions with comparators
     * @param  string $versions
     * @return string
     */
    protected static function standardizeMultipleComparators($versions)
    {
        $versions = preg_replace('/' . self::$global_single_comparator . self::$global_single_xrange . '/', '$1$2', $versions); //Paste comparator and version together
        //Condense multiple spaces to one
        $versions = preg_replace('/\\s+/', ' ', $versions);
        $or = explode('||', $versions);
        foreach ($or as &$orchunk) {
            $orchunk = trim($orchunk); //Remove spaces
            $and = explode(' ', $orchunk);
            foreach ($and as &$achunk) {
                $achunk = self::standardizeSingleComparator($achunk);
            }
            $orchunk = implode(' ', $and);
        }
        $versions = implode('||', $or);

        return $versions;
    }

    /**
     * standardizes a bunch of version ranges to comparators
     * @param  string          $range
     * @throws SemVerException
     * @return string
     */
    protected static function rangesToComparators($range)
    {
        $range_expression = sprintf(self::$range_mask, self::$global_single_version);
        $expression = sprintf(self::$regexp_mask, $range_expression);
        if (!preg_match($expression, $range)) {
            throw new SemVerException('Invalid range given', $range);
        }
        $versions = preg_replace($expression, '>=$1 <=$11', $range);
        $versions = self::standardizeMultipleComparators($versions);

        return $versions;
    }

    /**
     * standardizes a bunch of x-ranges to comparators
     * @param  string $ranges
     * @return string
     */
    protected static function xRangesToComparators($ranges)
    {
        $expression = sprintf(self::$regexp_mask, self::$global_single_xrange);

        return preg_replace_callback($expression, array('self', 'xRangesToComparatorsCallback'), $ranges);
    }

    /**
     * Callback for xRangesToComparators()
     * @internal
     * @param  array  $matches
     * @return string
     */
    private static function xRangesToComparatorsCallback($matches)
    {
        self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x');
        if ($build !== '') {
            $build = '-' . $build;
        }
        if ($major === 'x') {
            return '>=0';
        }
        if ($minor === 'x') {
            return '>=' . $major . ' <' . ($major + 1) . '.0.0-';
        }
        if ($patch === 'x') {
            return '>=' . $major . '.' . $minor . ' <' . $major . '.' . ($minor + 1) . '.0-';
        }

        return $major . '.' . $minor . '.' . $patch . $build . $prtag;
    }

    /**
     * standardizes a bunch of ~-ranges to comparators
     * @param  string $spermies
     * @return string
     */
    protected static function spermiesToComparators($spermies)
    {
        $expression = sprintf(self::$regexp_mask, self::$global_single_spermy . self::$global_single_xrange);

        return preg_replace_callback($expression, array('self', 'spermiesToComparatorsCallback'), $spermies);
    }

    /**
     * Callback for spermiesToComparators()
     * @internal
     * @param  unknown_type $matches
     * @return string
     */
    private static function spermiesToComparatorsCallback($matches)
    {
        self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x', 3);
        if ($build !== '') {
            $build = '-' . $build;
        }
        if ($major === 'x') {
            return '>=0';
        }
        if ($minor === 'x') {
            return '>=' . $major . ' <' . ($major + 1) . '.0.0-';
        }
        if ($patch === 'x') {
            return '>=' . $major . '.' . $minor . ' <' . $major . '.' . ($minor + 1) . '.0-';
        }

        return '>=' . $major . '.' . $minor . '.' . $patch . $build . $prtag . ' <' . $major . '.' . ($minor + 1) . '.0-';
    }

    /**
     * Standarizes a bunch of x-ranges with comparators in front of them to comparators
     *
     * @param  string $versions
     * @return string
     */
    private static function compAndxRangesToComparators($versions)
    {
        $regex = sprintf(self::$regexp_mask, self::$global_single_comparator.self::$global_single_xrange);

        return preg_replace_callback($regex, array('self', 'compAndxRangesToComparatorsCallback'), $versions);
    }

    /**
     * Callback for compAndxRangesToComparators()
     *
     * @internal
     * @param  array  $matches
     * @return string
     */
    private static function compAndxRangesToComparatorsCallback($matches)
    {
        $comparators = $matches[1];
        self::matchesToVersionParts($matches, $major, $minor, $patch, $build, $prtag, 'x', 3);
        if ($comparators[0] === '<') {
            if ($major === 'x') {
                return $comparators.'0';
            }
            if ($minor === 'x') {
                return $comparators.$major.'.0';
            }
            if ($patch === 'x') {
                return $comparators.$major.'.'.$minor.'.0';
            }

            return $comparators.self::constructVersionFromParts(false, $major, $minor, $patch, $build, $prtag);
        } elseif ($comparators[0] === '>') {
            return $comparators.self::constructVersionFromParts(false, ($major === 'x'?0:$major), ($minor === 'x'?0:$minor), ($patch === 'x'?0:$patch), $build, $prtag);
        }
    }

    /**
     * Converts matches to named version parts
     * @param array      $matches Matches array from preg_match
     * @param int|string $major   Reference to major version
     * @param int|string $minor   Reference to minor version
     * @param int|string $patch   Reference to patch version
     * @param int|string $build   Reference to build number
     * @param int|string $prtag   Reference to pre-release tags
     * @param int|string $default Default value for a version ifnot found in matches array
     * @param int        $offset  The position of the raw occurrence of the major version number
     */
    protected static function matchesToVersionParts($matches, &$major, &$minor, &$patch, &$build, &$prtag, $default = 0, $offset = 2)
    {
        $major = $minor = $patch = $default;
        $build = '';
        $prtag = '';
        switch (count($matches)) {
            default:
                /* no break */
            case $offset + 8:
                $prtag = $matches[$offset + 7];
            /* no break */
            case $offset + 7:
                $build = $matches[$offset + 6];
            /* no break */
            case $offset + 6:
                /* no break */
            case $offset + 5:
                $patch = $matches[$offset + 4];
            /* no break */
            case $offset + 4:
                /* no break */
            case $offset + 3:
                $minor = $matches[$offset + 2];
            /* no break */
            case $offset + 2:
                /* no break */
            case $offset + 1:
                $major = $matches[$offset];
            /* no break */
            case $offset:
                /* no break */
            case 0:
        }
        if (is_numeric($build)) {
            $build = intval($build);
        }
        if (is_numeric($patch)) {
            $patch = intval($patch);
        }
        if (is_numeric($minor)) {
            $minor = intval($minor);
        }
        if (is_numeric($major)) {
            $major = intval($major);
        }
    }

    /**
     * Converts all parameters to a version string
     * @param bool $padZero Pad the missing version parts with zeroes or not?
     * @param int  $ma      The major version number
     * @param int  $mi      The minor version number
     * @param int  $p       The patch number
     * @param int  $b       The build number
     * @param int  $t       The version tag
     * @return string
     */
    protected static function constructVersionFromParts($padZero = true, $ma = null, $mi = null, $p = null, $b = null, $t = null)
    {
        if ($padZero) {
            if ($ma === null) {
                return '0.0.0';
            }
            if ($mi === null) {
                return $ma.'.0.0';
            }
            if ($p === null) {
                return $ma.'.'.$mi.'.0';
            }
            if ($b === null && $t === null) {
                return $ma.'.'.$mi.'.'.$p;
            }
            if ($b !== null && $t === null) {
                return $ma.'.'.$mi.'.'.$p.'-'.$b;
            }
            if ($b === null && $t !== null) {
                return $ma.'.'.$mi.'.'.$p.$t;
            }
            if ($b !== null && $t !== null) {
                return $ma.'.'.$mi.'.'.$p.'-'.$b.$t;
            }
        } else {
            if ($ma === null) {
                return '';
            }
            if ($mi === null) {
                return $ma.'';
            }
            if ($p === null) {
                return $ma.'.'.$mi.'';
            }
            if ($b === null && $t === null) {
                return $ma.'.'.$mi.'.'.$p;
            }
            if ($b !== null && $t === null) {
                return $ma.'.'.$mi.'.'.$p.'-'.$b;
            }
            if ($b === null && $t !== null) {
                return $ma.'.'.$mi.'.'.$p.$t;
            }
            if ($b !== null && $t !== null) {
                return $ma.'.'.$mi.'.'.$p.'-'.$b.$t;
            }
        }
    }
}


class SemVer extends SemVerExpression
{

    private $version = '0.0.0';

    private $major = '0';

    private $minor = '0';

    private $patch = '0';

    private $build = '';

    private $prtag = '';

    /**
     * Initializes the version object with a simple version
     * @param  string          $version A simple, single version string
     * @param  bool            $padZero Set empty version pieces to zero?
     * @throws SemVerException
     */
    public function __construct($version, $padZero = false)
    {
        $version = (string) $version;
        $expression = sprintf(parent::$dirty_regexp_mask, parent::$global_single_version);
        if (!preg_match($expression, $version, $matches)) {
            throw new SemVerException('This is not a valid version');
        }

        parent::matchesToVersionParts($matches, $this->major, $this->minor, $this->patch, $this->build, $this->prtag, $padZero ? 0 : null);

        if ($this->build === '') {
            $this->build = null;
        }
        $this->version = parent::constructVersionFromParts($padZero, $this->major, $this->minor, $this->patch, $this->build, $this->prtag);

        if ($this->major === null) {
            $this->major = -1;
        }
        if ($this->minor === null) {
            $this->minor = -1;
        }
        if ($this->patch === null) {
            $this->patch = -1;
        }
        if ($this->build === null) {
            $this->build = -1;
        }
    }

    /**
     * Get the full version
     * @return string
     */
    public function getVersion()
    {
        return (string) $this->version;
    }

    /**
     * Get the major version number
     * @return int
     */
    public function getMajor()
    {
        return (int) $this->major;
    }

    /**
     * Get the minor version number
     * @return int
     */
    public function getMinor()
    {
        return (int) $this->minor;
    }

    /**
     * Get the patch version number
     * @return int
     */
    public function getPatch()
    {
        return (int) $this->patch;
    }

    /**
     * Get the build number
     * @return int
     */
    public function getBuild()
    {
        return (int) $this->build;
    }

    /**
     * Get the tag appended to the version
     * @return int
     */
    public function getTag()
    {
        return (string) $this->prtag;
    }

    /**
     * Returns a valid version
     * @return string
     * @see self::getVersion()
     */
    public function valid()
    {
        return $this->getVersion();
    }

    /**
     * Increment the version number
     * @param  string                         $what One of 'major', 'minor', 'patch' or 'build'
     * @return SemVer
     * @throws SemVerException                When an invalid increment value is given
     */
    public function inc($what)
    {
        if ($what == 'major') {
            return new SemVer(($this->major + 1) . '.0.0');
        }
        if ($what == 'minor') {
            return new SemVer($this->major . '.' . ($this->minor + 1) . '.0');
        }
        if ($what == 'patch') {
            return new SemVer($this->major . '.' . $this->minor . '.' . ($this->patch + 1));
        }
        if ($what == 'build') {
            if ($this->build == -1) {
                return new SemVer($this->major . '.' . $this->minor . '.' . $this->patch . '-1');
            }

            return new SemVer($this->major . '.' . $this->minor . '.' . $this->patch . '-' . ($this->build + 1));
        }
        throw new SemVerException('Invalid increment value given', $what);
    }

    /**
     * Checks whether this version satisfies an expression
     * @param  SemVerExpression $versions The expression to check against
     * @return bool
     */
    public function satisfies(SemVerExpression $versions)
    {
        return $versions->satisfiedBy($this);
    }

    public function __toString()
    {
        return $this->getVersion();
    }

    /**
     * Compare two versions
     * @param  string                   $v1  The first version
     * @param  string                   $cmp The comparator, one of '==', '!=', '>', '>=', '<', '<=', '===', '!=='
     * @param  string                   $v2  The second version
     * @return bool
     * @throws UnexpectedValueException
     */
    public static function cmp($v1, $cmp, $v2)
    {
        switch ($cmp) {
            case '==':
                return self::eq($v1, $v2);
            case '!=':
                return self::neq($v1, $v2);
            case '>':
                return self::gt($v1, $v2);
            case '>=':
                return self::gte($v1, $v2);
            case '<':
                return self::lt($v1, $v2);
            case '<=':
                return self::lte($v1, $v2);
            case '===':
                return $v1 === $v2;
            case '!==':
                return $v1 !== $v2;
            default:
                throw new UnexpectedValueException('Invalid comparator');
        }
    }

    /**
     * Checks ifa given string is greater than another
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return boolean
     */
    public static function gt($v1, $v2)
    {
        if (!$v1 instanceof SemVer) {
            $v1 = new SemVer($v1);
        }
        if (!$v2 instanceof SemVer) {
            $v2 = new SemVer($v2);
        }

        // Major version number
        $ma1 = $v1->getMajor();
        $ma2 = $v2->getMajor();

        if ($ma1 < 0 && $ma2 >= 0) {
            return false;
        }
        if ($ma1 >= 0 && $ma2 < 0) {
            return true;
        }
        if ($ma1 > $ma2) {
            return true;
        }
        if ($ma1 < $ma2) {
            return false;
        }

        // Minor version number
        $mi1 = $v1->getMinor();
        $mi2 = $v2->getMinor();

        if ($mi1 < 0 && $mi2 >= 0) {
            return false;
        }
        if ($mi1 >= 0 && $mi2 < 0) {
            return true;
        }
        if ($mi1 > $mi2) {
            return true;
        }
        if ($mi1 < $mi2) {
            return false;
        }

        // Patch level
        $p1 = $v1->getPatch();
        $p2 = $v2->getPatch();

        if ($p1 < 0 && $p2 >= 0) {
            return false;
        }
        if ($p1 >= 0 && $p2 < 0) {
            return true;
        }
        if ($p1 > $p2) {
            return true;
        }
        if ($p1 < $p2) {
            return false;
        }

        // Build number
        $b1 = $v1->getBuild();
        $b2 = $v2->getBuild();

        if ($b1 < 0 && $b2 >= 0) {
            return false;
        }
        if ($b1 >= 0 && $b2 < 0) {
            return true;
        }
        if ($b1 > $b2) {
            return true;
        }
        if ($b1 < $b2) {
            return false;
        }

        // Tag.
        $t1 = $v1->getTag();
        $t2 = $v2->getTag();

        if ($t1 === $t2) {
            return false;
        }
        if ($t1 === '' && $t2 !== '') {
            return true; //v1 has no tag, v2 has tag
        }
        if ($t1 !== '' && $t2 === '') {
            return false; //v1 has tag, v2 has no tag
        }

        // both have tags, sort them naturally to see which one is greater.
        $array = array($t1, $t2);
        natsort($array);

        // natsort() preserves array keys. $array[0] may not be the first element.
        return reset($array) === $t2;
    }

    /**
     * Checks ifa given string is greater than, or equal to another
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return boolean
     */
    public static function gte($v1, $v2)
    {
        return self::gt($v1, $v2) || self::eq($v1, $v2);
    }

    /**
     * Checks ifa given string is less than another
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return boolean
     */
    public static function lt($v1, $v2)
    {
        return self::gt($v2, $v1);
    }

    /**
     * Checks ifa given string is less than, or equal to another
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return boolean
     */
    public static function lte($v1, $v2)
    {
        return self::lt($v1, $v2) || self::eq($v1, $v2);
    }

    /**
     * Checks ifa given string is equal to another
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return boolean
     */
    public static function eq($v1, $v2)
    {
        if (!$v1 instanceof SemVer) {
            $v1 = new SemVer($v1, true);
        }
        if (!$v2 instanceof SemVer) {
            $v2 = new SemVer($v2, true);
        }

        return $v1->getVersion() === $v2->getVersion();
    }

    /**
     * Checks ifa given string is not equal to another
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return boolean
     */
    public static function neq($v1, $v2)
    {
        return !self::eq($v1, $v2);
    }

    /**
     * Compares two versions, can be used with usort()
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return int            0 when they are equal, -1 ifthe second version is smaller, 1 ifthe second version is greater
     */
    public static function compare($v1, $v2)
    {
        if (self::eq($v1, $v2)) {
            return 0;
        }
        if (self::gt($v1, $v2)) {
            return 1;
        }

        return -1;
    }

    /**
     * Reverse compares two versions, can be used with usort()
     * @param  string|SemVer $v1 The first version
     * @param  string|SemVer $v2 The second version
     * @return int            0 when they are equal, 1 ifthe second version is smaller, -1 ifthe second version is greater
     */
    public static function rcompare($v1, $v2)
    {
        return self::compare($v2, $v1);
    }

    /**
     * Shorthand function to match a version against an expression.
     * @param string|SemVer    $version The version to match.
     * @param string|SemVerExpression $range The expression to be matched against.
     * @return bool             True on a matching pair, false otherwise.
     */
    public static function satisfiesRange($version, $range)
    {
        if (!$version instanceof SemVer) {
            $version = new SemVer($version, true);
        }

        if (!$range instanceof SemVerExpression) {
            $range = new SemVerExpression($range);
        }

        return $version->satisfies($range);
    }
}

class SemVerException extends \RuntimeException
{
    protected $version = null;
    public function __construct($message, $version = null)
    {
        $this->version = $version;
        parent::__construct($message . ' [[' . $version . ']]');
    }
    public function getVersion()
    {
        return $this->version;
    }
}
