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

namespace webservice_loadedrest\format;

use coding_exception;
use Exception;
use external_description;
use XMLWriter;

defined('MOODLE_INTERNAL') || die;

/**
 * XML format.
 */
class xml_format extends abstract_format implements format {
    /**
     * XML version.
     *
     * @var string
     */
    const VERSION = '1.0';

    /**
     * XML document encoding.
     *
     * @var string
     */
    const ENCODING = 'utf-8';

    /**
     * @inheritdoc
     */
    public function get_name() {
        return 'xml';
    }

    /**
     * @inheritdoc
     */
    public function parse_request_body($body) {
        throw new coding_exception('urgh, xml');
    }

    /**
     * @inheritdoc
     */
    public function send_headers() {
        parent::send_headers();
        header('Content-Type: application/xml; charset=utf-8');
        header('Content-Disposition: inline; filename="response.xml"');
    }

    /**
     * @inheritdoc
     */
    public function send_error(Exception $exception) {
        $doc = new XMLWriter();
        $doc->openMemory();
        $doc->startDocument(static::VERSION, static::ENCODING);
            $doc->startElement('response');
                $doc->startElement('success');
                    $doc->text(0);
                $doc->endElement();
                $doc->startElement('exception');
                    $doc->startAttribute('class');
                        $doc->text(get_class($exception));
                    $doc->endAttribute();
                    $doc->startAttribute('code');
                        $doc->text($exception->getCode());
                    $doc->endAttribute();
                $doc->startElement('message');
                    $doc->text($exception->getMessage());
                $doc->endElement();

        if (debugging() && property_exists($exception, 'debuginfo')) {
            $doc->startElement('debug');
            /** @noinspection PhpUndefinedFieldInspection */
            $doc->text($exception->debuginfo);
            $doc->endElement();
        }

            $doc->endElement();
        $doc->endDocument();
        echo $doc->outputMemory();
    }

    /**
     * @inheritdoc
     */
    public function send_response($result, external_description $description) {
        $doc = new XMLWriter();
        $doc->openMemory();
        $doc->startDocument(static::VERSION, static::ENCODING);
        $this->to_xml($doc, $result, $description);
        $doc->endDocument();
        echo $doc->outputMemory();
    }

    /**
     * Dump the result to XML.
     *
     * @param XMLWriter $doc
     * @param $result
     * @param external_description $description
     */
    protected function to_xml(XMLWriter $doc, $result, external_description $description) {}
}
