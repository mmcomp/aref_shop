<?php

declare(strict_types=1);

namespace App\GraphQL\Types;

use App\Models\ReadingStation as ModelsReadingStation;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ReadingStationType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ReadingStation',
        'description' => 'Reading Station Type',
        'model' => ModelsReadingStation::class,
    ];

    public function fields(): array
    {
        return [
            'id'  => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The id of the reading station',
                // Use 'alias', if the database column is different from the type name.
                // This is supported for discrete values as well as relations.
                // - you can also use `DB::raw()` to solve more complex issues
                // - or a callback returning the value (string or `DB::raw()` result)
                // 'alias' => 'user_id',
            ],
            'name' =>  [
                'type' => Type::nonNull(Type::string()),
                'description' => 'The id of the reading station',
                // Use 'alias', if the database column is different from the type name.
                // This is supported for discrete values as well as relations.
                // - you can also use `DB::raw()` to solve more complex issues
                // - or a callback returning the value (string or `DB::raw()` result)
                // 'alias' => 'user_id',
            ],
        ];
    }
}
