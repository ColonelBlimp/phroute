<?php
declare(strict_types=1);
namespace Phroute\Phroute;

use Phroute\Phroute\Exception\BadRouteException;

/**
 * Parses routes of the following form:
 *
 * "/user/{name}/{id:[0-9]+}?"
 */
final class RouteParser
{
    /**
     * Search through the given route looking for dynamic portions.
     *
     * Using ~ as the regex delimiter.
     *
     * We start by looking for a literal '{' character followed by any amount of whitespace.
     * The next portion inside the parentheses looks for a parameter name containing alphanumeric characters or underscore.
     *
     * After this we look for the ':\d+' and ':[0-9]+' style portion ending with a closing '}' character.
     *
     * Finally we look for an optional '?' which is used to signify an optional route.
     */
    private const VARIABLE_REGEX =
"~\{
    \s* ([a-zA-Z0-9_]*) \s*
    (?:
        : \s* ([^{]+(?:\{.*?\})?)
    )?
\}\??~x";

    /**
     * The default parameter character restriction (One or more characters that is not a '/').
     */
    private const DEFAULT_DISPATCH_REGEX = '[^/]+';

    /**
     * @var array<mixed>
     */
    private $parts;

    /**
     * @var array<mixed>
     */
    private $reverseParts;

    /**
     * @var int
     */
    private $partsCounter;

    /**
     * @var array<mixed>
     */
    private $variables;

    /**
     * @var int
     */
    private $regexOffset;

    /**
     * Handy parameter type restrictions.
     * @var array<string>
     */
    private const REGEX_SHORTCUTS = [
        ':i}'  => ':[0-9]+}',
	    ':a}'  => ':[0-9A-Za-z]+}',
	    ':h}'  => ':[0-9A-Fa-f]+}',
        ':c}'  => ':[a-zA-Z0-9+_\-\.]+}'
    ];

    /**
     * Parse a route returning the correct data format to pass to the dispatch engine.
     * @param string $route
     * @return array<mixed>
     */
    function parse(string $route): array {
        $this->reset();

        $route = \strtr($route, self::REGEX_SHORTCUTS);

        if (!$matches = $this->extractVariableRouteParts($route)) {
            $reverse = [
                'variable'  => false,
                'value'     => $route];
            return [[$route], [$reverse]];
        }

        foreach ($matches as $set) {
            $this->staticParts($route, $set[0][1]);
            $this->validateVariable($set[1][0]);
            $regexPart = (isset($set[2]) ? \trim($set[2][0]) : self::DEFAULT_DISPATCH_REGEX);
            $this->regexOffset = $set[0][1] + \strlen($set[0][0]);
            $match = '(' . $regexPart . ')';
            $isOptional = \substr($set[0][0], -1) === '?';

            if ($isOptional) {
                $match = $this->makeOptional($match);
            }

            $this->reverseParts[$this->partsCounter] = [
                'variable'  => true,
                'optional'  => $isOptional,
                'name'      => $set[1][0]];

            $this->parts[$this->partsCounter++] = $match;
        }

        $this->staticParts($route, \strlen($route));
        return [[\implode('', $this->parts), $this->variables], \array_values($this->reverseParts)];
    }

    /**
     * Reset the parser ready for the next route.
     */
    private function reset(): void {
        $this->parts = [];
        $this->reverseParts = [];
        $this->partsCounter = 0;
        $this->variables = [];
        $this->regexOffset = 0;
    }

    /**
     * Return any variable route portions from the given route.
     * @param string $route
     * @return array<mixed>|null
     */
    private function extractVariableRouteParts(string $route): ?array {
        $matches = [];
        if (\preg_match_all(self::VARIABLE_REGEX, $route, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            return $matches;
        }
        return null;
    }

    /**
     * @param string $route
     * @param int $nextOffset
     */
    private function staticParts(string $route, int $nextOffset): void {
        $static = \preg_split('~(/)~u', \substr($route, $this->regexOffset, $nextOffset - $this->regexOffset), 0, PREG_SPLIT_DELIM_CAPTURE);
        if ($static === false) {
            throw new \ErrorException(__METHOD__ . ': preg_split() failed!');
        }
        foreach ($static as $staticPart) {
            if ($staticPart) {
                $quotedPart = \preg_quote($staticPart, '~');
                $this->parts[$this->partsCounter] = $quotedPart;
                $this->reverseParts[$this->partsCounter] = [
                    'variable'  => false,
                    'value'     => $staticPart];
                $this->partsCounter++;
            }
        }
    }

    /**
     * @param string $varName
     */
    private function validateVariable(string $varName): void {
        if (isset($this->variables[$varName])) {
            throw new BadRouteException("Cannot use the same placeholder '$varName' twice");
        }
        $this->variables[$varName] = $varName;
    }

    /**
     * @param string $match
     * @return string
     */
    private function makeOptional(string $match): string {
        $previous = $this->partsCounter - 1;
        if (isset($this->parts[$previous]) && $this->parts[$previous] === '/') {
            $this->partsCounter--;
            $match = '(?:/' . $match . ')';
        }
        return $match . '?';
    }
}
