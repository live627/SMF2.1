<?php

/**
 * Simple Machines Forum (SMF)
 *
 * @package SMF
 * @author Simple Machines https://www.simplemachines.org
 * @copyright 2026 Simple Machines and individual contributors
 * @license https://www.simplemachines.org/about/smf/license.php BSD
 *
 * @version 3.0 Alpha 4
 */

declare(strict_types=1);

namespace SMF\Who;

/**
 * Defines a batched lookup used by Who::determineActions().
 *
 * Implementations are responsible for:
 *  - Determining whether a set of request parameters applies to them.
 *  - Extracting the entity identifier to be resolved.
 *  - Providing the language string used when rendering the action.
 *  - Fetching all requested entities in a single query.
 *  - Providing formatting parameters for Lang::formatText().
 *
 * The typical workflow is:
 *
 *  1. determineActions() calls getId() for each request.
 *  2. Matching identifiers are grouped by lookup type.
 *  3. fetch() is called once with all requested identifiers.
 *  4. determineActions() calls getFormat() for each returned row.
 *  5. determineActions() renders the final string using getText().
 */
interface DataDrivenLookupInterface
{
	/**
	 * Extracts the identifier handled by this lookup.
	 *
	 * Returns null if the supplied request parameters do not match
	 * this lookup type.
	 *
	 * @param array $actions Parsed request parameters from log_online.url.
	 *
	 * @return int|null The identifier to queue, or null if unsupported.
	 */
	public function getId(array $actions, int $member_id): ?int;

	/**
	 * Returns the language string used when rendering results for
	 * this lookup.
	 *
	 * The returned value should typically be the result of
	 * Lang::getTxt().
	 *
	 * @param array $actions Parsed request parameters from log_online.url.
	 *
	 * @return string The language string template.
	 */
	public function getText(array $actions, int $member_id): string;

	/**
	 * Builds the formatting parameters used when rendering the final
	 * action string.
	 *
	 * The returned array will be passed to Lang::formatText().
	 *
	 * @param array $row A row returned by fetch().
	 *
	 * @return array Formatting parameters.
	 */
	public function getFormat(array $row): array;

	/**
	 * Fetches all requested entities in a single batch operation.
	 *
	 * Returned rows must be indexed by the identifier returned from
	 * getId().
	 *
	 * Example:
	 *
	 * [
	 *     123 => [
	 *         'id_topic' => 123,
	 *         'subject' => 'Welcome',
	 *     ],
	 * ]
	 *
	 * @param int[] $requested_ids Identifiers collected during processing.
	 *
	 * @return array<int, array> Rows indexed by identifier.
	 */
	public function fetch(array $requested_ids): array;
}