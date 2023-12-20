<?php

declare(strict_types=1);

namespace App\GraphQL\Queries;

use App\Models\ReadingStation;
use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Query;
use Rebing\GraphQL\Support\SelectFields;

class ReadingStationQuery extends Query
{
    protected $attributes = [
        'name' => 'readingStations',
        'description' => 'readingStation query'
    ];

    public function type(): Type
    {
        return Type::nonNull(Type::listOf(Type::nonNull(GraphQL::type('ReadingStation'))));
    }

    public function args(): array
    {
        return [
            'name' => [
                'name' => 'name', 
                'type' => Type::string(),
            ]
        ];

    }

    public function resolve($root, array $args, $context, ResolveInfo $resolveInfo, Closure $getSelectFields)
    {
        if (isset($args['name'])) {
            return ReadingStation::where('name' , 'like', '%' . $args['name'] . '%')->get();
        }

        return ReadingStation::all();
        // dd('a');
        // /** @var SelectFields $fields */
        // $fields = $getSelectFields();
        // $select = $fields->getSelect();
        // $with = $fields->getRelations();

        // return [
        //     'The readingStation works',
        // ];
    }
}
