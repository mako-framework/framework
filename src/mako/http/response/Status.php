<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

/**
 * HTTP status codes.
 */
enum Status: int
{
	// 1xx Informational

	case CONTINUE = 100;
	case SWITCHING_PROTOCOLS = 101;
	case PROCESSING = 102;
	case EARLY_HINTS = 103;

	// 2xx Success

	case OK = 200;
	case CREATED = 201;
	case ACCEPTED = 202;
	case NON_AUTHORITATIVE_INFORMATION = 203;
	case NO_CONTENT = 204;
	case RESET_CONTENT = 205;
	case PARTIAL_CONTENT = 206;
	case MULTI_STATUS = 207;
	case ALREADY_REPORTED = 208;
	case IM_USED = 226;

	// 3xx Redirection

	case MULTIPLE_CHOICES = 300;
	case MOVED_PERMANENTLY = 301;
	case FOUND = 302;
	case SEE_OTHER = 303;
	case NOT_MODIFIED = 304;
	case USE_PROXY = 305;
	case TEMPORARY_REDIRECT = 307;
	case PERMANENT_REDIRECT = 308;

	// 4xx Client Error

	case BAD_REQUEST = 400;
	case UNAUTHORIZED = 401;
	case PAYMENT_REQUIRED = 402;
	case FORBIDDEN = 403;
	case NOT_FOUND = 404;
	case METHOD_NOT_ALLOWED = 405;
	case NOT_ACCEPTABLE = 406;
	case PROXY_AUTHENTICATION_REQUIRED = 407;
	case REQUEST_TIMEOUT = 408;
	case CONFLICT = 409;
	case GONE = 410;
	case LENGTH_REQUIRED = 411;
	case PRECONDITION_FAILED = 412;
	case PAYLOAD_TOO_LARGE = 413;
	case URI_TOO_LONG = 414;
	case UNSUPPORTED_MEDIA_TYPE = 415;
	case RANGE_NOT_SATISFIABLE = 416;
	case EXPECTATION_FAILED = 417;
	case IM_A_TEAPOT = 418;
	case AUTENTICATION_TIMEOUT = 419;
	case MISDIRECTED_REQUEST = 421;
	case UNPROCESSABLE_ENTITY = 422;
	case LOCKED = 423;
	case FAILED_DEPENDENCY = 424;
	case TOO_EARLY = 425;
	case UPGRADE_REQUIRED = 426;
	case PRECONDITION_REQUIRED = 428;
	case TOO_MANY_REQUESTS = 429;
	case REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	case UNAVAILABLE_FOR_LEGAL_REASONS = 451;
	case INVALID_TOKEN = 498;
	case TOKEN_REQUIRED = 499;

	// 5xx Server Error

	case INTERNAL_SERVER_ERROR = 500;
	case NOT_IMPLEMENTED = 501;
	case BAD_GATEWAY = 502;
	case SERVICE_UNAVAILABLE = 503;
	case GATEWAY_TIMEOUT = 504;
	case HTTP_VERSION_NOT_SUPPORTED = 505;
	case VARIANT_ALSO_NEGOTIATES = 506;
	case INSUFFICIENT_STORAGE = 507;
	case LOOP_DETECTED = 508;
	case NOT_EXTENDED = 510;
	case NETWORK_AUTHENTICATION_REQUIRED = 511;

	/**
	 * Returns the status code.
	 */
	public function getStatusCode(): int
	{
		return $this->value;
	}

	/**
	 * Returns the header name.
	 */
	public function getHeaderName(): string
	{
		return match($this)
		{
			// 1xx Informational

			static::CONTINUE => 'Continue',
			static::SWITCHING_PROTOCOLS => 'Switching Protocols',
			static::PROCESSING => 'Processing',
			static::EARLY_HINTS => 'Early Hints',

			// 2xx Success

			static::OK => 'OK',
			static::CREATED => 'Created',
			static::ACCEPTED => 'Accepted',
			static::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
			static::NO_CONTENT => 'No Content',
			static::RESET_CONTENT => 'Reset Content',
			static::PARTIAL_CONTENT => 'Partial Content',
			static::MULTI_STATUS => 'Multi-Status',
			static::ALREADY_REPORTED => 'Already Reported',
			static::IM_USED => 'IM Used',

			// 3xx Redirection

			static::MULTIPLE_CHOICES => 'Multiple Choices',
			static::MOVED_PERMANENTLY => 'Moved Permanently',
			static::FOUND => 'Found',
			static::SEE_OTHER => 'See Other',
			static::NOT_MODIFIED => 'Not Modified',
			static::USE_PROXY => 'Use Proxy',
			static::TEMPORARY_REDIRECT => 'Temporary Redirect',
			static::PERMANENT_REDIRECT => 'Permanent Redirect',

			// 4xx Client Error

			static::BAD_REQUEST => 'Bad Request',
			static::UNAUTHORIZED => 'Unauthorized',
			static::PAYMENT_REQUIRED => 'Payment Required',
			static::FORBIDDEN => 'Forbidden',
			static::NOT_FOUND => 'Not Found',
			static::METHOD_NOT_ALLOWED => 'Method Not Allowed',
			static::NOT_ACCEPTABLE => 'Not Acceptable',
			static::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
			static::REQUEST_TIMEOUT => 'Request Timeout',
			static::CONFLICT => 'Conflict',
			static::GONE => 'Gone',
			static::LENGTH_REQUIRED => 'Length Required',
			static::PRECONDITION_FAILED => 'Precondition Failed',
			static::PAYLOAD_TOO_LARGE => 'Payload Too Large',
			static::URI_TOO_LONG => 'URI Too Long',
			static::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
			static::RANGE_NOT_SATISFIABLE => 'Range Not Satisfiable',
			static::EXPECTATION_FAILED => 'Expectation Failed',
			static::IM_A_TEAPOT => 'I\'m a teapot',
			static::AUTENTICATION_TIMEOUT => 'Authentication Timeout',
			static::MISDIRECTED_REQUEST => 'Misdirected Request',
			static::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
			static::LOCKED => 'Locked',
			static::FAILED_DEPENDENCY => 'Failed Dependency',
			static::TOO_EARLY => 'Too Early',
			static::UPGRADE_REQUIRED => 'Upgrade Required',
			static::PRECONDITION_REQUIRED => 'Precondition Required',
			static::TOO_MANY_REQUESTS => 'Too Many Requests',
			static::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
			static::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
			static::INVALID_TOKEN => 'Invalid Token',
			static::TOKEN_REQUIRED => 'Token Required',

			// 5xx Server Error

			static::INTERNAL_SERVER_ERROR => 'Internal Server Error',
			static::NOT_IMPLEMENTED => 'Not Implemented',
			static::BAD_GATEWAY => 'Bad Gateway',
			static::SERVICE_UNAVAILABLE => 'Service Unavailable',
			static::GATEWAY_TIMEOUT => 'Gateway Timeout',
			static::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
			static::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
			static::INSUFFICIENT_STORAGE => 'Insufficient Storage',
			static::LOOP_DETECTED => 'Loop Detected',
			static::NOT_EXTENDED => 'Not Extended',
			static::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
		};
	}
}
