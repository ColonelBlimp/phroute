<?php
declare(strict_types=1);
namespace Phroute\Phroute;

final class Route
{
    // Constants for before and after filters
    /**
     * @var string
     */
    const BEFORE = 'before';

    /**
     * @var string
     */
    const AFTER = 'after';

    /**
     * @var string
     */
    const PREFIX = 'prefix';

    // Constants for common HTTP methods
    /**
     * @var string
     */
    const ANY = 'ANY';

    /**
     * @var string
     */
    const GET = 'GET';

    /**
     * @var string
     */
    const HEAD = 'HEAD';

    /**
     * @var string
     */
    const POST = 'POST';

    /**
     * @var string
     */
    const PUT = 'PUT';

    /**
     * @var string
     */
    const PATCH = 'PATCH';

    /**
     * @var string
     */
    const DELETE = 'DELETE';

    /**
     * @var string
     */
    const OPTIONS = 'OPTIONS';
}
