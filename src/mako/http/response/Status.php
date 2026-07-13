<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use Deprecated;

/**
 * HTTP status codes.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status
 */
enum Status: int
{
	// 1xx Informational

	case Continue = 100;
	case SwitchingProtocols = 101;
	case Processing = 102;
	case EarlyHints = 103;

	// 2xx Success

	case Ok = 200;
	case Created = 201;
	case Accepted = 202;
	case NonAuthoritativeInformation = 203;
	case NoContent = 204;
	case ResetContent = 205;
	case PartialContent = 206;
	case MultiStatus = 207;
	case AlreadyReported = 208;
	case ImUsed = 226;

	// 3xx Redirection

	case MultipleChoices = 300;
	case MovedPermanently = 301;
	case Found = 302;
	case SeeOther = 303;
	case NotModified = 304;
	#[Deprecated('see https://datatracker.ietf.org/doc/html/rfc7231#section-6.4.5', 'Mako 13.0.0')]
	case UseProxy = 305;
	case TemporaryRedirect = 307;
	case PermanentRedirect = 308;

	// 4xx Client Error

	case BadRequest = 400;
	case Unauthorized = 401;
	case PaymentRequired = 402;
	case Forbidden = 403;
	case NotFound = 404;
	case MethodNotAllowed = 405;
	case NotAcceptable = 406;
	case ProxyAuthenticationRequired = 407;
	case RequestTimeout = 408;
	case Conflict = 409;
	case Gone = 410;
	case LengthRequired = 411;
	case PreconditionFailed = 412;
	case ContentTooLarge = 413;
	case UriTooLong = 414;
	case UnsupportedMediaType = 415;
	case RangeNotSatisfiable = 416;
	case ExpectationFailed = 417;
	case ImATeapot = 418;
	case MisdirectedRequest = 421;
	case UnprocessableContent = 422;
	case Locked = 423;
	case FailedDependency = 424;
	case TooEarly = 425;
	case UpgradeRequired = 426;
	case PreconditionRequired = 428;
	case TooManyRequests = 429;
	case RequestHeaderFieldsTooLarge = 431;
	case UnavailableForLegalReasons = 451;

	// 5xx Server Error

	case InternalServerError = 500;
	case NotImplemented = 501;
	case BadGateway = 502;
	case ServiceUnavailable = 503;
	case GatewayTimeout = 504;
	case HttpVersionNotSupported = 505;
	case VariantAlsoNegotiates = 506;
	case InsufficientStorage = 507;
	case LoopDetected = 508;
	case NotExtended = 510;
	case NetworkAuthenticationRequired = 511;

	/**
	 * Returns the status code.
	 */
	public function getCode(): int
	{
		return $this->value;
	}

	/**
	 * Returns the status message.
	 */
	public function getMessage(): string
	{
		return match ($this) {
			// 1xx Informational

			self::Continue => 'Continue',
			self::SwitchingProtocols => 'Switching Protocols',
			self::Processing => 'Processing',
			self::EarlyHints => 'Early Hints',

			// 2xx Success

			self::Ok => 'OK',
			self::Created => 'Created',
			self::Accepted => 'Accepted',
			self::NonAuthoritativeInformation => 'Non-Authoritative Information',
			self::NoContent => 'No Content',
			self::ResetContent => 'Reset Content',
			self::PartialContent => 'Partial Content',
			self::MultiStatus => 'Multi-Status',
			self::AlreadyReported => 'Already Reported',
			self::ImUsed => 'IM Used',

			// 3xx Redirection

			self::MultipleChoices => 'Multiple Choices',
			self::MovedPermanently => 'Moved Permanently',
			self::Found => 'Found',
			self::SeeOther => 'See Other',
			self::NotModified => 'Not Modified',
			@self::UseProxy => 'Use Proxy',
			self::TemporaryRedirect => 'Temporary Redirect',
			self::PermanentRedirect => 'Permanent Redirect',

			// 4xx Client Error

			self::BadRequest => 'Bad Request',
			self::Unauthorized => 'Unauthorized',
			self::PaymentRequired => 'Payment Required',
			self::Forbidden => 'Forbidden',
			self::NotFound => 'Not Found',
			self::MethodNotAllowed => 'Method Not Allowed',
			self::NotAcceptable => 'Not Acceptable',
			self::ProxyAuthenticationRequired => 'Proxy Authentication Required',
			self::RequestTimeout => 'Request Timeout',
			self::Conflict => 'Conflict',
			self::Gone => 'Gone',
			self::LengthRequired => 'Length Required',
			self::PreconditionFailed => 'Precondition Failed',
			self::ContentTooLarge => 'Content Too Large',
			self::UriTooLong => 'URI Too Long',
			self::UnsupportedMediaType => 'Unsupported Media Type',
			self::RangeNotSatisfiable => 'Range Not Satisfiable',
			self::ExpectationFailed => 'Expectation Failed',
			self::ImATeapot => 'I\'m a teapot',
			self::MisdirectedRequest => 'Misdirected Request',
			self::UnprocessableContent => 'Unprocessable Content',
			self::Locked => 'Locked',
			self::FailedDependency => 'Failed Dependency',
			self::TooEarly => 'Too Early',
			self::UpgradeRequired => 'Upgrade Required',
			self::PreconditionRequired => 'Precondition Required',
			self::TooManyRequests => 'Too Many Requests',
			self::RequestHeaderFieldsTooLarge => 'Request Header Fields Too Large',
			self::UnavailableForLegalReasons => 'Unavailable For Legal Reasons',

			// 5xx Server Error

			self::InternalServerError => 'Internal Server Error',
			self::NotImplemented => 'Not Implemented',
			self::BadGateway => 'Bad Gateway',
			self::ServiceUnavailable => 'Service Unavailable',
			self::GatewayTimeout => 'Gateway Timeout',
			self::HttpVersionNotSupported => 'HTTP Version Not Supported',
			self::VariantAlsoNegotiates => 'Variant Also Negotiates',
			self::InsufficientStorage => 'Insufficient Storage',
			self::LoopDetected => 'Loop Detected',
			self::NotExtended => 'Not Extended',
			self::NetworkAuthenticationRequired => 'Network Authentication Required',
		};
	}

	/**
	 * Returns TRUE if the status is informational and FALSE if not.
	 */
	public function isInformational(): bool
	{
		return $this->value >= 100 && $this->value <= 199;
	}

	/**
	 * Returns TRUE if the status is a success and FALSE if not.
	 */
	public function isSuccess(): bool
	{
		return $this->value >= 200 && $this->value <= 299;
	}

	/**
	 * Returns TRUE if the status is a redirect and FALSE if not.
	 */
	public function isRedirect(): bool
	{
		return $this->value >= 300 && $this->value <= 399;
	}

	/**
	 * Returns TRUE if the status is a client error and FALSE if not.
	 */
	public function isClientError(): bool
	{
		return $this->value >= 400 && $this->value <= 499;
	}

	/**
	 * Returns TRUE if the status is a server error and FALSE if not.
	 */
	public function isServerError(): bool
	{
		return $this->value >= 500 && $this->value <= 599;
	}
}
