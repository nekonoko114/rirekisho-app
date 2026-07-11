<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Shared create/diff-sync logic for child collections submitted as arrays of
 * rows (histories, licenses). $mapRow converts one input row to an attribute
 * array for the related model, or returns null to skip the row.
 */
trait SyncsChildRecords
{
    /**
     * Blind-insert child rows (used on document creation).
     *
     * @param  callable(array, int): ?array  $mapRow
     */
    protected function createChildren(HasMany $relation, array $rows, callable $mapRow): void
    {
        foreach (array_values($rows) as $i => $row) {
            $data = $mapRow((array) $row, $i);
            if ($data !== null) {
                $relation->create($data);
            }
        }
    }

    /**
     * Differential sync (used on document update): incoming rows carrying an
     * existing `id` update that record, rows without one are inserted, and
     * existing records absent from the payload are deleted.
     *
     * @param  callable(array, int): ?array  $mapRow
     */
    protected function syncChildren(HasMany $relation, array $rows, callable $mapRow): void
    {
        $existing = $relation->get()->keyBy('id');
        $seenIds = [];

        foreach (array_values($rows) as $i => $row) {
            $row = (array) $row;
            $data = $mapRow($row, $i);
            if ($data === null) {
                continue;
            }

            if (! empty($row['id']) && isset($existing[$row['id']])) {
                $existing[$row['id']]->update($data);
                $seenIds[] = $row['id'];
            } else {
                $relation->create($data);
            }
        }

        $toDelete = $existing->keys()->diff($seenIds);
        if ($toDelete->isNotEmpty()) {
            $relation->getRelated()->newQuery()->whereIn('id', $toDelete->all())->delete();
        }
    }
}
