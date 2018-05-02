<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Loaded REST.
 *
 * @package webservice_loadedrest
 * @author Luke Carrier <luke@carrier.im>
 * @copyright 2018 Luke Carrier
 */

namespace webservice_loadedrest;

defined('MOODLE_INTERNAL') || die;

/**
 * Class webservice_loadedrest_client
 */
class client {
    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';

    protected $baseurl;
    protected $token;
    protected $format;

    public function __construct($baseurl, $token, $format=null) {
        $format = $format ?? static::FORMAT_XML;
    }

    public function call($httpmethod, $wsfunction, $params) {}
}
