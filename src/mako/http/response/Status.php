<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\http\response;

use Deprecated;

/**
 * HTTP status codes.
 */
enum Status: int
{
	/* Start compatibility */
	#[Deprecated('use Status::Continue instead', 'Mako 12.2.0')]
	public const CONTINUE = self::Continue;
	#[Deprecated('use Status::SwitchingProtocols instead', 'Mako 12.2.0')]
	public const SWITCHING_PROTOCOLS = self::SwitchingProtocols;
	#[Deprecated('use Status::Processing instead', 'Mako 12.2.0')]
	public const PROCESSING = self::Processing;
	#[Deprecated('use Status::EarlyHints instead', 'Mako 12.2.0')]
	public const EARLY_HINTS = self::EarlyHints;
	#[Deprecated('use Status::Ok instead', 'Mako 12.2.0')]
	public const OK = self::Ok;
	#[Deprecated('use Status::Created instead', 'Mako 12.2.0')]
	public const CREATED = self::Created;
	#[Deprecated('use Status::Accepted instead', 'Mako 12.2.0')]
	public const ACCEPTED = self::Accepted;
	#[Deprecated('use Status::NonAuthoritativeInformation instead', 'Mako 12.2.0')]
	public const NON_AUTHORITATIVE_INFORMATION = self::NonAuthoritativeInformation;
	#[Deprecated('use Status::NoContent instead', 'Mako 12.2.0')]
	public const NO_CONTENT = self::NoContent;
	#[Deprecated('use Status::ResetContent instead', 'Mako 12.2.0')]
	public const RESET_CONTENT = self::ResetContent;
	#[Deprecated('use Status::PartialContent instead', 'Mako 12.2.0')]
	public const PARTIAL_CONTENT = self::PartialContent;
	#[Deprecated('use Status::MultiStatus instead', 'Mako 12.2.0')]
	public const MULTI_STATUS = self::MultiStatus;
	#[Deprecated('use Status::AlreadyReported instead', 'Mako 12.2.0')]
	public const ALREADY_REPORTED = self::AlreadyReported;
	#[Deprecated('use Status::ImUsed instead', 'Mako 12.2.0')]
	public const IM_USED = self::ImUsed;
	#[Deprecated('use Status::MultipleChoices instead', 'Mako 12.2.0')]
	public const MULTIPLE_CHOICES = self::MultipleChoices;
	#[Deprecated('use Status::MovedPermanently instead', 'Mako 12.2.0')]
	public const MOVED_PERMANENTLY = self::MovedPermanently;
	#[Deprecated('use Status::Found instead', 'Mako 12.2.0')]
	public const FOUND = self::Found;
	#[Deprecated('use Status::SeeOther instead', 'Mako 12.2.0')]
	public const SEE_OTHER = self::SeeOther;
	#[Deprecated('use Status::NotModified instead', 'Mako 12.2.0')]
	public const NOT_MODIFIED = self::NotModified;
	#[Deprecated('use Status::UseProxy instead', 'Mako 12.2.0')]
	public const USE_PROXY = self::UseProxy;
	#[Deprecated('use Status::TemporaryRedirect instead', 'Mako 12.2.0')]
	public const TEMPORARY_REDIRECT = self::TemporaryRedirect;
	#[Deprecated('use Status::PermanentRedirect instead', 'Mako 12.2.0')]
	public const PERMANENT_REDIRECT = self::PermanentRedirect;
	#[Deprecated('use Status::BadRequest instead', 'Mako 12.2.0')]
	public const BAD_REQUEST = self::BadRequest;
	#[Deprecated('use Status::Unauthorized instead', 'Mako 12.2.0')]
	public const UNAUTHORIZED = self::Unauthorized;
	#[Deprecated('use Status::PaymentRequired instead', 'Mako 12.2.0')]
	public const PAYMENT_REQUIRED = self::PaymentRequired;
	#[Deprecated('use Status::Forbidden instead', 'Mako 12.2.0')]
	public const FORBIDDEN = self::Forbidden;
	#[Deprecated('use Status::NotFound instead', 'Mako 12.2.0')]
	public const NOT_FOUND = self::NotFound;
	#[Deprecated('use Status::MethodNotAllowed instead', 'Mako 12.2.0')]
	public const METHOD_NOT_ALLOWED = self::MethodNotAllowed;
	#[Deprecated('use Status::NotAcceptable instead', 'Mako 12.2.0')]
	public const NOT_ACCEPTABLE = self::NotAcceptable;
	#[Deprecated('use Status::ProxyAuthenticationRequired instead', 'Mako 12.2.0')]
	public const PROXY_AUTHENTICATION_REQUIRED = self::ProxyAuthenticationRequired;
	#[Deprecated('use Status::RequestTimeout instead', 'Mako 12.2.0')]
	public const REQUEST_TIMEOUT = self::RequestTimeout;
	#[Deprecated('use Status::Conflict instead', 'Mako 12.2.0')]
	public const CONFLICT = self::Conflict;
	#[Deprecated('use Status::Gone instead', 'Mako 12.2.0')]
	public const GONE = self::Gone;
	#[Deprecated('use Status::LengthRequired instead', 'Mako 12.2.0')]
	public const LENGTH_REQUIRED = self::LengthRequired;
	#[Deprecated('use Status::PreconditionFailed instead', 'Mako 12.2.0')]
	public const PRECONDITION_FAILED = self::PreconditionFailed;
	#[Deprecated('use Status::PayloadTooLarge instead', 'Mako 12.2.0')]
	public const PAYLOAD_TOO_LARGE = self::PayloadTooLarge;
	#[Deprecated('use Status::UriTooLong instead', 'Mako 12.2.0')]
	public const URI_TOO_LONG = self::UriTooLong;
	#[Deprecated('use Status::UnsupportedMediaType instead', 'Mako 12.2.0')]
	public const UNSUPPORTED_MEDIA_TYPE = self::UnsupportedMediaType;
	#[Deprecated('use Status::RangeNotSatisfiable instead', 'Mako 12.2.0')]
	public const RANGE_NOT_SATISFIABLE = self::RangeNotSatisfiable;
	#[Deprecated('use Status::ExpectationFailed instead', 'Mako 12.2.0')]
	public const EXPECTATION_FAILED = self::ExpectationFailed;
	#[Deprecated('use Status::ImATeapot instead', 'Mako 12.2.0')]
	public const IM_A_TEAPOT = self::ImATeapot;
	#[Deprecated('use Status::AuthenticationTimeout instead', 'Mako 12.2.0')]
	public const AUTHENTICATION_TIMEOUT = self::AuthenticationTimeout;
	#[Deprecated('use Status::MisdirectedRequest instead', 'Mako 12.2.0')]
	public const MISDIRECTED_REQUEST = self::MisdirectedRequest;
	#[Deprecated('use Status::UnprocessableEntity instead', 'Mako 12.2.0')]
	public const UNPROCESSABLE_ENTITY = self::UnprocessableEntity;
	#[Deprecated('use Status::Locked instead', 'Mako 12.2.0')]
	public const LOCKED = self::Locked;
	#[Deprecated('use Status::FailedDependency instead', 'Mako 12.2.0')]
	public const FAILED_DEPENDENCY = self::FailedDependency;
	#[Deprecated('use Status::TooEarly instead', 'Mako 12.2.0')]
	public const TOO_EARLY = self::TooEarly;
	#[Deprecated('use Status::UpgradeRequired instead', 'Mako 12.2.0')]
	public const UPGRADE_REQUIRED = self::UpgradeRequired;
	#[Deprecated('use Status::PreconditionRequired instead', 'Mako 12.2.0')]
	public const PRECONDITION_REQUIRED = self::PreconditionRequired;
	#[Deprecated('use Status::TooManyRequests instead', 'Mako 12.2.0')]
	public const TOO_MANY_REQUESTS = self::TooManyRequests;
	#[Deprecated('use Status::RequestHeaderFieldsTooLarge instead', 'Mako 12.2.0')]
	public const REQUEST_HEADER_FIELDS_TOO_LARGE = self::RequestHeaderFieldsTooLarge;
	#[Deprecated('use Status::UnavailableForLegalReasons instead', 'Mako 12.2.0')]
	public const UNAVAILABLE_FOR_LEGAL_REASONS = self::UnavailableForLegalReasons;
	#[Deprecated('use Status::InvalidToken instead', 'Mako 12.2.0')]
	public const INVALID_TOKEN = self::InvalidToken;
	#[Deprecated('use Status::TokenRequired instead', 'Mako 12.2.0')]
	public const TOKEN_REQUIRED = self::TokenRequired;
	#[Deprecated('use Status::InternalServerError instead', 'Mako 12.2.0')]
	public const INTERNAL_SERVER_ERROR = self::InternalServerError;
	#[Deprecated('use Status::NotImplemented instead', 'Mako 12.2.0')]
	public const NOT_IMPLEMENTED = self::NotImplemented;
	#[Deprecated('use Status::BadGateway instead', 'Mako 12.2.0')]
	public const BAD_GATEWAY = self::BadGateway;
	#[Deprecated('use Status::ServiceUnavailable instead', 'Mako 12.2.0')]
	public const SERVICE_UNAVAILABLE = self::ServiceUnavailable;
	#[Deprecated('use Status::GatewayTimeout instead', 'Mako 12.2.0')]
	public const GATEWAY_TIMEOUT = self::GatewayTimeout;
	#[Deprecated('use Status::HttpVersionNotSupported instead', 'Mako 12.2.0')]
	public const HTTP_VERSION_NOT_SUPPORTED = self::HttpVersionNotSupported;
	#[Deprecated('use Status::VariantAlsoNegotiates instead', 'Mako 12.2.0')]
	public const VARIANT_ALSO_NEGOTIATES = self::VariantAlsoNegotiates;
	#[Deprecated('use Status::InsufficientStorage instead', 'Mako 12.2.0')]
	public const INSUFFICIENT_STORAGE = self::InsufficientStorage;
	#[Deprecated('use Status::LoopDetected instead', 'Mako 12.2.0')]
	public const LOOP_DETECTED = self::LoopDetected;
	#[Deprecated('use Status::NotExtended instead', 'Mako 12.2.0')]
	public const NOT_EXTENDED = self::NotExtended;
	#[Deprecated('use Status::NetworkAuthenticationRequired instead', 'Mako 12.2.0')]
	public const NETWORK_AUTHENTICATION_REQUIRED = self::NetworkAuthenticationRequired;
	/* End compatibility */

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
	case PayloadTooLarge = 413;
	case UriTooLong = 414;
	case UnsupportedMediaType = 415;
	case RangeNotSatisfiable = 416;
	case ExpectationFailed = 417;
	case ImATeapot = 418;
	case AuthenticationTimeout = 419;
	case MisdirectedRequest = 421;
	case UnprocessableEntity = 422;
	case Locked = 423;
	case FailedDependency = 424;
	case TooEarly = 425;
	case UpgradeRequired = 426;
	case PreconditionRequired = 428;
	case TooManyRequests = 429;
	case RequestHeaderFieldsTooLarge = 431;
	case UnavailableForLegalReasons = 451;
	case InvalidToken = 498;
	case TokenRequired = 499;

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
			self::UseProxy => 'Use Proxy',
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
			self::PayloadTooLarge => 'Payload Too Large',
			self::UriTooLong => 'URI Too Long',
			self::UnsupportedMediaType => 'Unsupported Media Type',
			self::RangeNotSatisfiable => 'Range Not Satisfiable',
			self::ExpectationFailed => 'Expectation Failed',
			self::ImATeapot => 'I\'m a teapot',
			self::AuthenticationTimeout => 'Authentication Timeout',
			self::MisdirectedRequest => 'Misdirected Request',
			self::UnprocessableEntity => 'Unprocessable Entity',
			self::Locked => 'Locked',
			self::FailedDependency => 'Failed Dependency',
			self::TooEarly => 'Too Early',
			self::UpgradeRequired => 'Upgrade Required',
			self::PreconditionRequired => 'Precondition Required',
			self::TooManyRequests => 'Too Many Requests',
			self::RequestHeaderFieldsTooLarge => 'Request Header Fields Too Large',
			self::UnavailableForLegalReasons => 'Unavailable For Legal Reasons',
			self::InvalidToken => 'Invalid Token',
			self::TokenRequired => 'Token Required',

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
}
