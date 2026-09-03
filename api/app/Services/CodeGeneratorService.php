<?php

namespace App\Services;

use App\Models\Counter;

class CodeGeneratorService
{
    /** Atomic Mongo counter: PRD-00001, USR-00001, PRF-00001. */
    public function next(string $prefix): string
    {
        $result = Counter::raw(function ($collection) use ($prefix) {
            return $collection->findOneAndUpdate(
                ['_id' => $prefix],
                ['$inc' => ['seq' => 1]],
                ['upsert' => true, 'returnDocument' => \MongoDB\Operation\FindOneAndUpdate::RETURN_DOCUMENT_AFTER]
            );
        });

        $sequence = is_array($result)
            ? (int) ($result['seq'] ?? 1)
            : (int) ($result->seq ?? 1);

        return sprintf('%s-%05d', $prefix, $sequence);
    }
}
