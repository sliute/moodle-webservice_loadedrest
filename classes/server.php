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

use external_api;
use invalid_response_exception;
use webservice_base_server;
use webservice_loadedrest\format\format_factory;

defined('MOODLE_INTERNAL') || die;
require_once "{$CFG->dirroot}/webservice/lib.php";

/**
 * Webservice server.
 */
class server extends webservice_base_server {
    /**
     * Moodle component namre.
     *
     * @var string
     */
    const COMPONENT_NAME = 'webservice_loadedrest';

    /**
     * Web service protocol name.
     *
     * @var string
     */
    const PROTOCOL_NAME = 'loadedrest';

    /**
     * HTTP status line: 403 forbidden.
     *
     * @var string
     */
    const HTTP_STATUS_FORBIDDEN = '%s 403 Forbidden';

    /**
     * Parameter: body format.
     *
     * @var string
     */
    const PARAM_BODYFORMAT = 'wsformat';

    /**
     * Parameter: function name.
     *
     * @var string
     */
    const PARAM_FUNCTION = 'wsfunction';

    /**
     * Parameter: authentication token.
     *
     * @var string
     */
    const PARAM_TOKEN = 'wstoken';

    /**
     * REST request and response body format.
     *
     * One of the FORMAT_* values.
     *
     * @var string
     */
    protected $formatname;

    /**
     * REST request and response body format.
     *
     * @var \webservice_loadedrest\format\format
     */
    protected $format;

    /**
     * Is the protocol enabled?
     *
     * @return bool
     */
    public static function is_enabled() {
        return webservice_protocol_is_enabled(static::PROTOCOL_NAME);
    }

    /**
     * 403 and exit unless the protocol is enabled.
     *
     * @return void
     */
    public static function require_enabled() {
        if (!static::is_enabled()) {
            // Guard against misconfigured FastCGI parameters
            $protocol = array_key_exists('SERVER_PROTOCOL', $_SERVER)
                    ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';
            header(sprintf(static::HTTP_STATUS_FORBIDDEN, $protocol));
            exit;
        }
    }

    /**
     * Initialiser.
     */
    public function __construct() {
        parent::__construct(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
        $this->wsname = static::PROTOCOL_NAME;
        $this->formatname = format_factory::FORMAT_DEFAULT;
    }

    /**
     * @inheritdoc
     */
    protected function parse_request() {
        parent::set_web_service_call_settings();
        $params = array_merge($_GET, $_POST);

        if (array_key_exists(static::PARAM_BODYFORMAT, $params)) {
            $this->formatname = $params[static::PARAM_BODYFORMAT];
        }
        $this->format = format_factory::create_or_default($this->formatname);

        if (array_key_exists(static::PARAM_TOKEN, $params)) {
            $this->token = $params[static::PARAM_TOKEN];
        }
        if (array_key_exists(static::PARAM_FUNCTION, $params)) {
            $this->functionname = $params[static::PARAM_FUNCTION];
        }

        $body = file_get_contents('php://input');
        $this->parameters = $this->format->parse_request_body($body);
    }

    /**
     * @inheritdoc
     */
    protected function send_response() {
        $cleanvalue = null;
        try {
            if ($this->function->returns_desc !== null) {
                $cleanvalue = external_api::clean_returnvalue(
                        $this->function->returns_desc, $this->returns);
            }
        } catch (invalid_response_exception $e) {
            $this->format->send_headers();
            $this->format->send_error($e);
            return;
        }

        $this->format->send_headers();
        $this->format->send_response($cleanvalue, $this->function->returns_desc);
    }

    /**
     * @inheritdoc
     */
    protected function send_error($exception=null) {
        $this->format->send_headers();
        $this->format->send_error($exception);
    }
}
